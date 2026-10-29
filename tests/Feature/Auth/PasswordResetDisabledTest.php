<?php

use Laravel\Fortify\Features;

test('password reset routes are unavailable', function () {
    expect(Features::enabled(Features::resetPasswords()))->toBeFalse();

    $this->get('/forgot-password')->assertNotFound();
    $this->post('/forgot-password', ['email' => 'admin@example.com'])->assertNotFound();
    $this->get('/reset-password/some-token')->assertNotFound();
    $this->post('/reset-password', ['token' => 'some-token'])->assertNotFound();
});

test('the login page does not offer a password reset link', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertDontSee('Forgot your password?')
        ->assertDontSee('/forgot-password', false);
});

test('the admin bootstrap command points password resets at itself', function () {
    $this->artisan('app:make-admin', [
        'email' => 'owner@example.com',
        '--name' => 'Portfolio Owner',
        '--password' => 'correct-horse-battery',
    ])
        ->expectsOutputToContain('run php artisan app:make-admin owner@example.com again.')
        ->doesntExpectOutputToContain('/forgot-password')
        ->assertSuccessful();
});
