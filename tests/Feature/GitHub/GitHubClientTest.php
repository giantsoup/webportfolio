<?php

use App\Services\GitHub\GitHubClient;
use App\Services\GitHub\GitHubRepository;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

test('it lists the account public repositories with the token and api version headers', function () {
    fakeGitHub([githubRepositoryPayload('webportfolio', [
        'homepage' => 'https://example.com',
        'topics' => ['laravel', 'livewire'],
        'stargazers_count' => 12,
    ])]);

    $repositories = app(GitHubClient::class)->repositories();

    expect($repositories)->toHaveCount(1)
        ->and($repositories->first())->toBeInstanceOf(GitHubRepository::class)
        ->and($repositories->first()->fullName)->toBe('giantsoup/webportfolio')
        ->and($repositories->first()->homepage)->toBe('https://example.com')
        ->and($repositories->first()->topics)->toBe(['laravel', 'livewire'])
        ->and($repositories->first()->stars)->toBe(12)
        ->and($repositories->first()->pushedAt?->toDateString())->toBe('2026-10-01');

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'users/giantsoup/repos')
        && str_contains($request->url(), 'type=owner')
        && $request->hasHeader('Authorization', 'Bearer test-token')
        && $request->hasHeader('X-GitHub-Api-Version', '2022-11-28'));
});

test('it follows pagination until a short page', function () {
    config(['services.github.username' => 'giantsoup']);

    Http::fake(fn (Request $request) => Http::response(match ((int) $request['page']) {
        1 => array_map(fn (int $index): array => githubRepositoryPayload("repo-{$index}"), range(1, 100)),
        2 => [githubRepositoryPayload('repo-101')],
    }));

    expect(app(GitHubClient::class)->repositories())->toHaveCount(101);

    Http::assertSentCount(2);
    Http::assertSent(fn (Request $request): bool => ! $request->hasHeader('Authorization'));
});

test('it stops paginating after ten pages', function () {
    config(['services.github.username' => 'giantsoup']);

    Http::fake(fn () => Http::response(array_map(fn (int $index): array => githubRepositoryPayload("repo-{$index}"), range(1, 100))));

    expect(app(GitHubClient::class)->repositories())->toHaveCount(1000);

    Http::assertSentCount(10);
});

test('it caches the listing until refreshed', function () {
    fakeGitHub([githubRepositoryPayload('webportfolio')]);

    $github = app(GitHubClient::class);
    $github->repositories();
    $github->repositories();

    Http::assertSentCount(1);

    $github->refresh();
    $github->repositories();

    Http::assertSentCount(2);
});

test('it finds a repository by its full name regardless of case', function () {
    fakeGitHub([githubRepositoryPayload('WebPortfolio')]);

    $github = app(GitHubClient::class);

    expect($github->repository('giantsoup/webportfolio')?->name)->toBe('WebPortfolio')
        ->and($github->repository('someone-else/webportfolio'))->toBeNull();
});

test('it ignores homepages that are not urls', function () {
    fakeGitHub([githubRepositoryPayload('webportfolio', ['homepage' => 'not a url'])]);

    expect(app(GitHubClient::class)->repositories()->first()->homepage)->toBeNull();
});

test('it lists nothing without a configured account', function () {
    config(['services.github.username' => null]);

    $github = app(GitHubClient::class);

    expect($github->isConfigured())->toBeFalse()
        ->and($github->repositories())->toBeEmpty();
});

test('it throws when github rejects the request', function () {
    config(['services.github.username' => 'giantsoup', 'services.github.token' => 'expired']);
    Http::fake(['api.github.com/*' => Http::response(['message' => 'Bad credentials'], 401)]);

    app(GitHubClient::class)->repositories();
})->throws(RequestException::class);
