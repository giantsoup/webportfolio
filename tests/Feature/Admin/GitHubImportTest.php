<?php

use App\Livewire\Admin\Projects\Import;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('the import page lists repositories and hides forks and archived ones by default', function () {
    fakeGitHub([
        githubRepositoryPayload('webportfolio', ['topics' => ['laravel']]),
        githubRepositoryPayload('forked-thing', ['fork' => true]),
        githubRepositoryPayload('old-thing', ['archived' => true]),
    ]);

    $this->get(route('admin.projects.import'))
        ->assertSuccessful()
        ->assertSee('Import from GitHub')
        ->assertSee('webportfolio')
        ->assertSee(route('admin.projects.create', ['repository' => 'giantsoup/webportfolio']), false)
        ->assertDontSee('forked-thing')
        ->assertDontSee('old-thing');

    Livewire::test(Import::class)
        ->set('showForks', true)
        ->set('showArchived', true)
        ->assertSee('forked-thing')
        ->assertSee('old-thing');
});

test('the import page filters by search across names, descriptions and topics', function () {
    fakeGitHub([
        githubRepositoryPayload('webportfolio', ['topics' => ['livewire']]),
        githubRepositoryPayload('signal-board', ['language' => 'TypeScript']),
    ]);

    Livewire::test(Import::class)
        ->set('search', 'LIVEWIRE')
        ->assertSee('webportfolio')
        ->assertDontSee('signal-board')
        ->set('search', 'nothing-matches')
        ->assertSee('No repositories match these filters.');
});

test('imported repositories link to their project instead of importing again', function () {
    fakeGitHub([githubRepositoryPayload('webportfolio')]);
    $project = Project::factory()->create(['github_repository' => 'giantsoup/webportfolio']);

    Livewire::test(Import::class)
        ->assertSee('Imported')
        ->assertSee(route('admin.projects.edit', $project), false)
        ->assertDontSee(route('admin.projects.create', ['repository' => 'giantsoup/webportfolio']), false);
});

test('a project whose repository url matches can be linked from the import page', function () {
    fakeGitHub([githubRepositoryPayload('signal-board', ['stargazers_count' => 7, 'topics' => ['xml']])]);
    $project = Project::factory()->create(['repo_url' => 'https://github.com/giantsoup/signal-board']);

    Livewire::test(Import::class)
        ->assertSeeHtml("wire:click=\"link('giantsoup\\/signal-board')\"")
        ->call('link', 'giantsoup/signal-board')
        ->assertSee('Imported');

    $project->refresh();

    expect($project->github_repository)->toBe('giantsoup/signal-board')
        ->and($project->github_stars)->toBe(7)
        ->and($project->github_topics)->toBe(['xml'])
        ->and($project->github_synced_at)->not->toBeNull();
});

test('the import page explains when github is not configured', function () {
    config(['services.github.username' => null]);

    $this->get(route('admin.projects.import'))
        ->assertSuccessful()
        ->assertSee('GitHub is not connected yet');
});

test('the import page explains when github cannot be reached', function () {
    config(['services.github.username' => 'giantsoup', 'services.github.token' => 'expired']);
    Http::fake(['api.github.com/*' => Http::response(['message' => 'Bad credentials'], 401)]);

    $this->get(route('admin.projects.import'))
        ->assertSuccessful()
        ->assertSee('GitHub could not be reached');
});

test('the projects index links to the github import page', function () {
    $this->get(route('admin.projects.index'))
        ->assertSee(route('admin.projects.import'), false);
});
