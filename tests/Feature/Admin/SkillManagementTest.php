<?php

use App\Livewire\Admin\Skills\Form;
use App\Models\User;
use Livewire\Livewire;

test('admin users can create a skill from the livewire form', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Form::class)
        ->set('name', 'Laravel Architecture')
        ->set('slug', 'laravel-architecture')
        ->set('category', 'Backend')
        ->set('icon', 'server-stack')
        ->set('accent_color', '#00eefc')
        ->set('sort_order', 2)
        ->set('is_featured', true)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('skills', [
        'slug' => 'laravel-architecture',
        'is_featured' => true,
    ]);
});
