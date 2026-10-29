<?php

use App\Livewire\Admin\Projects\Import;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use App\Services\GitHub\GitHubClient;
use App\Services\GitHub\GitHubProjectImporter;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

test('import all creates unpublished drafts and links projects that already point at a repository', function () {
    fakeGitHub([
        githubRepositoryPayload('signal-board', ['topics' => ['laravel'], 'language' => 'PHP']),
        githubRepositoryPayload('linked-already'),
        githubRepositoryPayload('url-match', ['description' => null]),
    ], ['PHP' => 900, 'Blade' => 100], "# Signal Board\n\nA real README paragraph.");

    $laravel = Skill::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    Project::factory()->create(['github_repository' => 'giantsoup/linked-already']);
    $urlMatch = Project::factory()->create(['title' => 'Hand Written', 'repo_url' => 'https://github.com/giantsoup/url-match']);

    $result = app(GitHubProjectImporter::class)->importAll(app(GitHubClient::class)->repositories());

    expect($result)->toBe(['created' => 1, 'linked' => 1]);

    $draft = Project::query()->where('github_repository', 'giantsoup/signal-board')->firstOrFail();

    expect($draft->is_published)->toBeFalse()
        ->and($draft->published_at)->toBeNull()
        ->and($draft->title)->toBe('Signal Board')
        ->and($draft->summary)->toBe('The signal-board repository.')
        ->and($draft->body)->toBe("Signal Board\n\nA real README paragraph.")
        ->and($draft->category)->toBe('PHP')
        ->and($draft->github_readme)->toBe("# Signal Board\n\nA real README paragraph.")
        ->and($draft->skills->pluck('id')->all())->toBe([$laravel->id]);

    $urlMatch->refresh();

    expect($urlMatch->title)->toBe('Hand Written')
        ->and($urlMatch->github_repository)->toBe('giantsoup/url-match');

    expect(Project::query()->published()->where('github_repository', 'giantsoup/signal-board')->exists())->toBeFalse();
});

test('imported drafts get a placeholder summary and a unique slug', function () {
    fakeGitHub([githubRepositoryPayload('signal-board', ['description' => null])]);
    Project::factory()->create(['slug' => 'signal-board']);

    app(GitHubProjectImporter::class)->importAll(app(GitHubClient::class)->repositories());

    $draft = Project::query()->where('github_repository', 'giantsoup/signal-board')->firstOrFail();

    expect($draft->slug)->toBe('signal-board-2')
        ->and($draft->summary)->toBe(GitHubProjectImporter::PLACEHOLDER_SUMMARY)
        ->and($draft->body)->toBe(GitHubProjectImporter::PLACEHOLDER_SUMMARY);
});

test('import all from the picker only imports the repositories currently listed', function () {
    $this->actingAs(User::factory()->create());

    fakeGitHub([
        githubRepositoryPayload('signal-board'),
        githubRepositoryPayload('forked-thing', ['fork' => true]),
    ]);

    Livewire::test(Import::class)
        ->assertSee('Import all (1)')
        ->call('importAll')
        ->assertSee('Created 1 draft project(s) and linked 0 existing project(s).')
        ->assertSee('Import all (0)');

    expect(Project::query()->where('github_repository', 'giantsoup/signal-board')->exists())->toBeTrue()
        ->and(Project::query()->where('github_repository', 'giantsoup/forked-thing')->exists())->toBeFalse();
});

test('import all reports when github cannot be reached', function () {
    $this->actingAs(User::factory()->create());

    config(['services.github.username' => 'giantsoup']);
    Http::fake([
        'api.github.com/users/giantsoup/repos*' => Http::response([githubRepositoryPayload('signal-board')]),
        'api.github.com/repos/*' => Http::response(['message' => 'Server Error'], 500),
    ]);

    Livewire::test(Import::class)
        ->call('importAll')
        ->assertSee('GitHub could not be reached. Try again in a moment.');

    expect(Project::query()->where('github_repository', 'giantsoup/signal-board')->exists())->toBeFalse();
});

test('details are fetched in parallel with the raw readme accept header and a missing readme is null', function () {
    config(['services.github.username' => 'giantsoup']);
    Http::fake([
        'api.github.com/users/giantsoup/repos*' => Http::response([githubRepositoryPayload('one'), githubRepositoryPayload('two')]),
        'api.github.com/repos/giantsoup/one/readme' => Http::response('# One'),
        'api.github.com/repos/giantsoup/two/readme' => Http::response(['message' => 'Not Found'], 404),
        'api.github.com/repos/*/languages' => Http::response(['Go' => 10]),
    ]);

    $github = app(GitHubClient::class);
    $details = $github->detailsFor($github->repositories());

    expect($details['giantsoup/one']->readme)->toBe('# One')
        ->and($details['giantsoup/one']->languages)->toBe(['Go' => 10])
        ->and($details['giantsoup/two']->readme)->toBeNull();

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/readme')
        && $request->hasHeader('Accept', 'application/vnd.github.raw+json'));
});

test('readme markdown becomes plain paragraphs', function (string $markdown, string $expected) {
    expect(GitHubProjectImporter::plainTextFromMarkdown($markdown))->toBe($expected);
})->with([
    'headings and paragraphs' => ["# Title\n\nSome **bold** text\nwrapped.", "Title\n\nSome bold text wrapped."],
    'lists' => ["- One\n- Two", "One\n\nTwo"],
    'badges and code are dropped' => ["[![CI](https://x.test/b.svg)](https://x.test)\n\nUsage:\n\n```bash\nnpm install\n```", 'Usage:'],
    'raw html is dropped' => ["<script>alert(1)</script>\n\nSafe text.", 'Safe text.'],
    'empty' => ['', ''],
]);
