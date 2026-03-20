<?php

use App\Models\PortfolioSetting;
use App\Models\Project;
use App\Models\Skill;

test('public portfolio pages render published content', function () {
    PortfolioSetting::factory()->create();

    $skills = Skill::factory()->count(2)->create();
    $publishedProject = Project::factory()->featured()->create([
        'title' => 'Telemetry Hub',
    ]);
    $publishedProject->skills()->sync($skills->pluck('id'));

    $draftProject = Project::factory()->unpublished()->create([
        'title' => 'Hidden Draft',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Housing Compliance Platform')
        ->assertSee('Sole In-House Developer, 2020 to Present')
        ->assertSee('CDLAC')
        ->assertSee('Telemetry Hub')
        ->assertDontSee('Hidden Draft');

    $this->get(route('projects.index'))
        ->assertOk()
        ->assertSee('Telemetry Hub')
        ->assertDontSee('Hidden Draft');

    $this->get(route('projects.show', $publishedProject))
        ->assertOk()
        ->assertSee('Telemetry Hub');

    $this->get(route('contact.create'))
        ->assertOk()
        ->assertSee('Get in');

    $this->get(route('projects.show', $draftProject))
        ->assertNotFound();
});
