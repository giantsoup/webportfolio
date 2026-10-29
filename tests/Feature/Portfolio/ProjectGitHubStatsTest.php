<?php

use App\Models\Project;

test('published project pages show github stats for linked projects', function () {
    $project = Project::factory()->create([
        'github_repository' => 'giantsoup/signal-board',
        'github_stars' => 1,
        'github_forks' => 12,
        'github_language' => 'TypeScript',
        'github_pushed_at' => '2026-09-14 10:00:00',
        'github_synced_at' => now(),
    ]);

    $this->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertSee('data-project-github', false)
        ->assertSeeInOrder(['On GitHub', '1 star', '12 forks', 'TypeScript', 'Last push', 'Sep 2026']);
});

test('project pages hide the github fact for unlinked projects', function () {
    $project = Project::factory()->create(['github_synced_at' => null]);

    $this->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertDontSee('data-project-github', false)
        ->assertDontSee('On GitHub');
});

test('zero star and fork counts are left off the github fact', function () {
    $project = Project::factory()->create([
        'github_repository' => 'giantsoup/signal-board',
        'github_stars' => 0,
        'github_forks' => 0,
        'github_language' => 'Go',
        'github_synced_at' => now(),
    ]);

    $this->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertSeeInOrder(['On GitHub', 'Go'])
        ->assertDontSee('0 stars')
        ->assertDontSee('0 forks');
});
