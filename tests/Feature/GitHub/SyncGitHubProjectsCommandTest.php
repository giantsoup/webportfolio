<?php

use App\Models\Project;
use Illuminate\Support\Facades\Http;

test('it refreshes linked projects and links projects by repository url', function () {
    fakeGitHub([
        githubRepositoryPayload('webportfolio', ['stargazers_count' => 5]),
        githubRepositoryPayload('signal-board', ['stargazers_count' => 8]),
    ]);

    $linked = Project::factory()->create(['github_repository' => 'giantsoup/webportfolio', 'github_stars' => 1]);
    $byUrl = Project::factory()->create(['repo_url' => 'https://github.com/giantsoup/signal-board']);
    $unrelated = Project::factory()->create(['repo_url' => null]);

    $this->artisan('app:sync-github-projects')
        ->expectsOutputToContain('Synced 2 project(s) from GitHub.')
        ->assertSuccessful();

    expect($linked->refresh()->github_stars)->toBe(5)
        ->and($byUrl->refresh()->github_repository)->toBe('giantsoup/signal-board')
        ->and($byUrl->github_stars)->toBe(8)
        ->and($unrelated->refresh()->github_repository)->toBeNull();
});

test('it warns about linked repositories that are no longer public', function () {
    fakeGitHub([]);
    Project::factory()->create(['github_repository' => 'giantsoup/gone']);

    $this->artisan('app:sync-github-projects')
        ->expectsOutputToContain('giantsoup/gone is no longer a public repository of giantsoup.')
        ->assertSuccessful();
});

test('it fails when github cannot be reached', function () {
    config(['services.github.username' => 'giantsoup']);
    Http::fake(['api.github.com/*' => Http::response(['message' => 'Bad credentials'], 401)]);

    $this->artisan('app:sync-github-projects')->assertFailed();
});

test('it does nothing without a configured account', function () {
    config(['services.github.username' => null]);

    $this->artisan('app:sync-github-projects')
        ->expectsOutputToContain('GITHUB_USERNAME is not set')
        ->assertSuccessful();
});
