<?php

use App\Models\PortfolioSetting;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Support\Facades\Vite;

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
        ->assertSee(Vite::asset('resources/images/logo.png'), false)
        ->assertSee('href="/favicon.ico"', false)
        ->assertSee('href="/favicon-32x32.png"', false)
        ->assertSee('href="/favicon-16x16.png"', false)
        ->assertSee('href="/apple-touch-icon.png"', false)
        ->assertDontSee('href="/favicon.svg"', false)
        ->assertSee('Focused on long-term product systems')
        ->assertSee('Laravel architecture, operational software, and durable internal tools.')
        ->assertSee('Housing Compliance Platform')
        ->assertSee('Since 2020, I have served as the sole in-house developer')
        ->assertSee('Sole In-House Developer, 2020 to Present')
        ->assertSee('I lead the long-term technical direction and day-to-day engineering execution')
        ->assertSee('This is not just a feature-delivery problem.')
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
        ->assertSee('Get in')
        ->assertSee('Focused on long-term product systems')
        ->assertSee('Reach out for relevant introductions, product conversations, or thoughtful professional inquiries.')
        ->assertDontSee('Available for new opportunities');

    $this->get(route('projects.show', $draftProject))
        ->assertNotFound();
});
