<?php

use App\Livewire\Admin\Projects\Form;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Livewire\Livewire;

test('admin users can create a project from the livewire form', function () {
    $user = User::factory()->create();
    $skill = Skill::factory()->create();

    $this->actingAs($user);

    Livewire::test(Form::class)
        ->set('title', 'New Platform Build')
        ->set('slug', 'new-platform-build')
        ->set('summary', 'A concise summary of the engagement.')
        ->set('body', 'A longer project description for the case study page.')
        ->set('category', 'Custom Laravel')
        ->set('is_published', true)
        ->set('is_featured', true)
        ->set('sort_order', 1)
        ->set('skill_ids', [$skill->id])
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('projects', [
        'slug' => 'new-platform-build',
        'is_published' => true,
        'is_featured' => true,
    ]);

    expect(Project::query()->whereSlug('new-platform-build')->firstOrFail()->skills)->toHaveCount(1);
});
