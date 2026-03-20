<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('admin users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('non admin users can not visit the dashboard', function () {
    $user = User::factory()->state(['is_admin' => false])->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertForbidden();
});
