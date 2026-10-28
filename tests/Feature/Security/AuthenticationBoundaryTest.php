<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

afterEach(function () {
    Http::assertNothingSent();
});

beforeEach(function () {
    Http::preventStrayRequests();
});

test('login throttling prevents repeated invalid authentication', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'invalid-password'])
            ->assertSessionHasErrors('email');
    }

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertTooManyRequests();

    $this->assertGuest();
});

test('a valid recovery code completes two factor authentication and cannot be reused', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('two-factor.login'));
    $this->assertGuest();

    $this->post(route('two-factor.login.store'), ['recovery_code' => 'invalid-recovery-code'])
        ->assertSessionHasErrors('recovery_code');
    $this->assertGuest();

    $this->post(route('two-factor.login.store'), ['recovery_code' => 'recovery-code-1'])
        ->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);

    expect($user->fresh()->recoveryCodes())->not->toContain('recovery-code-1');

    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('two-factor.login'));
    $this->post(route('two-factor.login.store'), ['recovery_code' => 'recovery-code-1'])
        ->assertSessionHasErrors('recovery_code');
    $this->assertGuest();
});

test('user mass assignment cannot grant admin access or verify an email', function () {
    $user = User::query()->create([
        'name' => 'Synthetic User',
        'email' => 'ordinary@example.test',
        'password' => 'password',
        'is_admin' => true,
        'email_verified_at' => now(),
    ]);

    expect($user->fresh()->is_admin)->toBeFalse();
    expect($user->fresh()->email_verified_at)->toBeNull();

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
});
