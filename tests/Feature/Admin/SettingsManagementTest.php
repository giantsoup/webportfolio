<?php

use App\Livewire\Admin\Settings\Edit;
use App\Models\User;
use Livewire\Livewire;

test('admin users can update portfolio settings', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Edit::class)
        ->set('hero_kicker', 'Open for select builds')
        ->set('hero_title', 'Designing')
        ->set('hero_emphasis', 'Durable')
        ->set('hero_summary', 'A revised homepage summary for the portfolio.')
        ->set('availability_text', 'Available for high-leverage Laravel work.')
        ->set('years_experience', 12)
        ->set('projects_completed', 99)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('portfolio_settings', [
        'hero_title' => 'Designing',
        'projects_completed' => 99,
    ]);
});
