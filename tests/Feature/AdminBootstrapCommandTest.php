<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('admin bootstrap command creates a verified admin user', function () {
    $this->artisan('app:make-admin', [
        'email' => 'owner@example.com',
        '--name' => 'Site Owner',
        '--password' => 'Sup3rSecurePass!',
    ])->assertExitCode(0);

    $user = User::query()->firstWhere('email', 'owner@example.com');

    expect($user)->not->toBeNull();
    expect($user->isAdmin())->toBeTrue();
    expect($user->hasVerifiedEmail())->toBeTrue();
    expect(Hash::check('Sup3rSecurePass!', $user->password))->toBeTrue();
});

test('admin bootstrap command can promote an existing user and send verification email', function () {
    Notification::fake();

    $user = User::factory()->create([
        'name' => 'Existing User',
        'email' => 'owner@example.com',
        'email_verified_at' => null,
        'is_admin' => false,
        'password' => Hash::make('ExistingPass123!'),
    ]);

    $this->artisan('app:make-admin', [
        'email' => $user->email,
        '--unverified' => true,
        '--send-verification' => true,
    ])
        ->expectsConfirmation('Set a new password for this account?', 'no')
        ->assertExitCode(0);

    $user->refresh();

    expect($user->name)->toBe('Existing User');
    expect($user->isAdmin())->toBeTrue();
    expect($user->hasVerifiedEmail())->toBeFalse();
    expect(Hash::check('ExistingPass123!', $user->password))->toBeTrue();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('database seeder does not create a default admin login', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('email', 'test@example.com')->exists())->toBeFalse();
});
