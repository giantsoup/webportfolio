<?php

use App\Livewire\Admin\Projects\Form;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('importing a repository prefills the project form', function () {
    fakeGitHub([githubRepositoryPayload('trench-tracker', [
        'description' => 'Campaign tracker for tabletop games.',
        'homepage' => 'https://trench.example.com',
        'topics' => ['laravel', 'tailwindcss'],
    ])], ['PHP' => 900, 'Blade' => 100]);

    $laravel = Skill::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $tailwind = Skill::factory()->create(['name' => 'Tailwind CSS', 'slug' => 'tailwind-css']);
    Skill::factory()->create(['name' => 'MySQL', 'slug' => 'mysql']);

    $this->get(route('admin.projects.create', ['repository' => 'giantsoup/trench-tracker']))->assertSuccessful();

    Livewire::withQueryParams(['repository' => 'giantsoup/trench-tracker'])
        ->test(Form::class)
        ->assertSet('title', 'Trench Tracker')
        ->assertSet('slug', 'trench-tracker')
        ->assertSet('summary', 'Campaign tracker for tabletop games.')
        ->assertSet('body', 'Campaign tracker for tabletop games.')
        ->assertSet('repo_url', 'https://github.com/giantsoup/trench-tracker')
        ->assertSet('live_url', 'https://trench.example.com')
        ->assertSet('github_repository', 'giantsoup/trench-tracker')
        ->assertSet('skill_ids', [$laravel->id, $tailwind->id]);
});

test('importing a repository the account does not own shows an error', function () {
    fakeGitHub([githubRepositoryPayload('webportfolio')]);

    Livewire::withQueryParams(['repository' => 'someone-else/secret'])
        ->test(Form::class)
        ->assertHasErrors('github_repository')
        ->assertSet('title', '');
});

test('saving a linked project records its github stats', function () {
    fakeGitHub([githubRepositoryPayload('trench-tracker', [
        'stargazers_count' => 21,
        'forks_count' => 4,
        'language' => 'PHP',
        'topics' => ['laravel'],
    ])]);

    Livewire::withQueryParams(['repository' => 'giantsoup/trench-tracker'])
        ->test(Form::class)
        ->call('save')
        ->assertHasNoErrors();

    $project = Project::query()->whereSlug('trench-tracker')->firstOrFail();

    expect($project->github_repository)->toBe('giantsoup/trench-tracker')
        ->and($project->github_stars)->toBe(21)
        ->and($project->github_forks)->toBe(4)
        ->and($project->github_language)->toBe('PHP')
        ->and($project->github_topics)->toBe(['laravel'])
        ->and($project->github_pushed_at?->toDateString())->toBe('2026-10-01');
});

test('an existing project can be linked, synced and unlinked from the edit form', function () {
    config(['services.github.username' => 'giantsoup']);
    Http::fake([
        'api.github.com/users/giantsoup/repos*' => Http::sequence()
            ->push([githubRepositoryPayload('signal-board', ['stargazers_count' => 2])])
            ->push([githubRepositoryPayload('signal-board', ['stargazers_count' => 9])]),
        'api.github.com/repos/*/languages' => Http::response(['PHP' => 100]),
        'api.github.com/repos/*/readme' => Http::sequence()
            ->push('# Signal Board v1')
            ->push('# Signal Board v2'),
    ]);

    $project = Project::factory()->create();

    Livewire::test(Form::class, ['project' => $project])
        ->assertSee('Not linked')
        ->set('github_repository', 'giantsoup/signal-board')
        ->call('save')
        ->assertHasNoErrors();

    expect($project->refresh()->github_stars)->toBe(2);

    Livewire::test(Form::class, ['project' => $project])
        ->call('syncGitHub')
        ->assertHasNoErrors();

    expect($project->refresh()->github_stars)->toBe(9)
        ->and($project->github_readme)->toBe('# Signal Board v2');

    Livewire::test(Form::class, ['project' => $project])
        ->set('github_repository', '')
        ->call('save')
        ->assertHasNoErrors();

    $project->refresh();

    expect($project->github_repository)->toBeNull()
        ->and($project->github_stars)->toBeNull()
        ->and($project->github_readme)->toBeNull()
        ->and($project->github_synced_at)->toBeNull();
});

test('linking a repository that is not on github is rejected', function () {
    fakeGitHub([githubRepositoryPayload('webportfolio')]);
    $project = Project::factory()->create(['github_repository' => null]);

    Livewire::test(Form::class, ['project' => $project])
        ->set('github_repository', 'giantsoup/missing')
        ->call('save')
        ->assertHasErrors('github_repository');

    expect($project->refresh()->github_repository)->toBeNull();
});

test('a repository can only be linked to one project', function () {
    fakeGitHub([githubRepositoryPayload('webportfolio')]);
    Project::factory()->create(['github_repository' => 'giantsoup/webportfolio']);

    Livewire::test(Form::class, ['project' => Project::factory()->create()])
        ->set('github_repository', 'giantsoup/webportfolio')
        ->call('save')
        ->assertHasErrors(['github_repository' => 'unique']);
});

test('saving copy edits does not call github when the link is unchanged', function () {
    fakeGitHub([]);
    $project = Project::factory()->create(['github_repository' => 'giantsoup/webportfolio']);
    Http::fake(['*' => Http::response([], 500)]);

    Livewire::test(Form::class, ['project' => $project])
        ->set('summary', 'Updated summary.')
        ->call('save')
        ->assertHasNoErrors();

    expect($project->refresh()->summary)->toBe('Updated summary.')
        ->and($project->github_repository)->toBe('giantsoup/webportfolio');
});

test('the slug follows the title only until it is edited by hand', function () {
    Livewire::test(Form::class)
        ->set('title', 'First Title')
        ->assertSet('slug', 'first-title')
        ->set('slug', 'custom-slug')
        ->set('title', 'Second Title')
        ->assertSet('slug', 'custom-slug');
});

test('repository urls are parsed into github repository names', function (?string $url, ?string $expected) {
    expect(Project::gitHubRepositoryFromUrl($url))->toBe($expected);
})->with([
    'plain' => ['https://github.com/giantsoup/xmlviewer', 'giantsoup/xmlviewer'],
    'trailing slash' => ['https://github.com/giantsoup/xmlviewer/', 'giantsoup/xmlviewer'],
    'git suffix' => ['https://github.com/giantsoup/xmlviewer.git', 'giantsoup/xmlviewer'],
    'www' => ['https://www.github.com/giantsoup/dbgold', 'giantsoup/dbgold'],
    'deep link' => ['https://github.com/giantsoup/xmlviewer/tree/main', null],
    'other host' => ['https://gitlab.com/giantsoup/xmlviewer', null],
    'empty' => [null, null],
]);

test('the stored readme can be previewed and used as the body', function () {
    $project = Project::factory()->create([
        'body' => 'Original body.',
        'github_repository' => 'giantsoup/signal-board',
        'github_synced_at' => now(),
        'github_readme' => "# Signal Board\n\nTracks <b>signals</b>.",
    ]);

    Livewire::test(Form::class, ['project' => $project])
        ->assertSee('View README')
        ->assertSeeHtml('<h1>Signal Board</h1>')
        ->assertDontSeeHtml('<b>signals</b>')
        ->call('useReadmeAsBody')
        ->assertSet('body', "Signal Board\n\nTracks signals.");

    expect($project->refresh()->body)->toBe('Original body.');
});

test('importing a repository uses its readme for the body', function () {
    fakeGitHub([githubRepositoryPayload('signal-board')], readme: "# Signal Board\n\nFirst paragraph.\n\nSecond paragraph.");

    Livewire::withQueryParams(['repository' => 'giantsoup/signal-board'])
        ->test(Form::class)
        ->assertSet('summary', 'The signal-board repository.')
        ->assertSet('body', "Signal Board\n\nFirst paragraph.\n\nSecond paragraph.");
});
