<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Inquiries\Index as InquiryIndex;
use App\Livewire\Admin\Projects\Index as ProjectIndex;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin pages render inside the themed back office layout', function (string $routeName, string $heading) {
    $this->get(route($routeName))
        ->assertSuccessful()
        ->assertSee('class="admin ', false)
        ->assertSee('Back office')
        ->assertSee($heading)
        ->assertDontSee('Starter Docs');
})->with([
    'dashboard' => ['dashboard', 'Portfolio overview'],
    'projects' => ['admin.projects.index', 'Projects'],
    'create project' => ['admin.projects.create', 'Create project'],
    'skills' => ['admin.skills.index', 'Skills'],
    'create skill' => ['admin.skills.create', 'Create skill'],
    'inquiries' => ['admin.inquiries.index', 'Inquiries'],
    'portfolio settings' => ['admin.settings.edit', 'Portfolio settings'],
]);

test('the dashboard lists the latest inquiries with new ones first', function () {
    Inquiry::factory()->create(['name' => 'Archived Sender', 'status' => 'archived']);
    Inquiry::factory()->create(['name' => 'Fresh Sender', 'status' => 'new']);

    Livewire::test(Dashboard::class)
        ->assertViewHas('recentInquiries', fn ($inquiries) => $inquiries->first()->name === 'Fresh Sender')
        ->assertSeeInOrder(['Latest inquiries', 'Fresh Sender', 'Archived Sender']);
});

test('the dashboard shows an empty state without inquiries', function () {
    Livewire::test(Dashboard::class)
        ->assertSee('No inquiries found.');
});

test('the project list shows status badges and row actions', function () {
    $project = Project::factory()->featured()->create(['title' => 'Signal Board']);

    $this->get(route('admin.projects.index'))
        ->assertSuccessful()
        ->assertSee('Signal Board')
        ->assertSee('/work/'.$project->slug)
        ->assertSee('Published')
        ->assertSee('Featured')
        ->assertSee(route('admin.projects.edit', $project), false)
        ->assertSee('Delete Signal Board');
});

test('the project form lists skills and submits from the page header', function () {
    Skill::factory()->create(['name' => 'Pest Testing']);

    $this->get(route('admin.projects.create'))
        ->assertSuccessful()
        ->assertSee('Pest Testing')
        ->assertSee('form="project-form"', false);
});

test('the inquiry list shows the status badge and status actions', function () {
    $inquiry = Inquiry::factory()->create(['status' => 'reviewed']);

    Livewire::test(InquiryIndex::class)
        ->assertSee($inquiry->email)
        ->assertSee('Reviewed')
        ->assertSeeHtml("wire:click=\"markStatus({$inquiry->id}, 'contacted')\"");
});

test('the inquiry list shows an empty state for a filtered status with no results', function () {
    Inquiry::factory()->create(['status' => 'new']);

    Livewire::test(InquiryIndex::class)
        ->set('status', 'archived')
        ->assertSee('No inquiries found.');
});

test('the project list pages through projects with flux pagination', function () {
    Project::factory()->count(13)->sequence(fn ($sequence) => ['title' => "Paged Project {$sequence->index}", 'sort_order' => $sequence->index, 'is_featured' => false])->create();

    $component = Livewire::test(ProjectIndex::class)
        ->assertSeeHtml('wire:click="nextPage(\'page\')"');

    $firstPage = $component->viewData('projects')->pluck('id');

    $component->call('gotoPage', 2)
        ->assertHasNoErrors();

    expect($component->viewData('projects')->currentPage())->toBe(2)
        ->and($component->viewData('projects')->pluck('id')->intersect($firstPage))->toBeEmpty();
});

test('changing the inquiry status filter returns to the first page', function () {
    Inquiry::factory()->count(16)->create(['status' => 'new']);

    Livewire::test(InquiryIndex::class)
        ->call('gotoPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('status', 'new')
        ->assertSet('paginators.page', 1);
});
