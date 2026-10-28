<?php

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;

beforeEach(function () {
    Http::preventStrayRequests();
    Mail::fake();
});

function accessBoundarySnapshot(TestResponse $response, ?string $componentName = null): string
{
    $response->assertOk();

    preg_match_all('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);

    foreach ($matches[1] as $encodedSnapshot) {
        $snapshot = html_entity_decode($encodedSnapshot, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($componentName === null || json_decode($snapshot, true)['memo']['name'] === $componentName) {
            return $snapshot;
        }
    }

    throw new RuntimeException('Expected component snapshot was not rendered.');
}

function accessBoundaryPayload(string $snapshot, string $method, array $parameters = []): array
{
    return [
        'components' => [[
            'snapshot' => $snapshot,
            'updates' => (object) [],
            'calls' => [['path' => '', 'method' => $method, 'params' => $parameters]],
        ]],
    ];
}

test('livewire inquiry writes enforce current admin and verification status', function (string $access) {
    $admin = User::factory()->admin()->create();
    $inquiry = Inquiry::factory()->create(['status' => 'new', 'contacted_at' => null]);
    $snapshot = accessBoundarySnapshot($this->actingAs($admin)->get(route('admin.inquiries.index')));

    if ($access === 'revoked') {
        $admin->forceFill(['is_admin' => false])->save();
    } elseif ($access === 'unverified') {
        $admin->forceFill(['email_verified_at' => null])->save();
    }

    $this->actingAs($admin->fresh());

    $response = $this->postJson(Livewire::getUpdateUri(), accessBoundaryPayload(
        $snapshot, 'markStatus', [$inquiry->id, 'contacted'],
    ), ['X-Livewire' => 'true']);

    if ($access === 'permitted') {
        $response->assertOk();
        expect($inquiry->fresh()->status)->toBe('contacted');
        expect($inquiry->fresh()->contacted_at)->not->toBeNull();
    } else {
        $response->assertForbidden();
        expect($inquiry->fresh()->status)->toBe('new');
        expect($inquiry->fresh()->contacted_at)->toBeNull();
        $response->assertDontSee($inquiry->message);
    }
})->with(['permitted', 'revoked', 'unverified']);

test('livewire security actions require a current password confirmation', function (bool $confirmed, int $timeout) {
    config(['auth.password_timeout' => $timeout]);

    $admin = User::factory()->admin()->withTwoFactor()->create();
    $snapshot = accessBoundarySnapshot(
        $this->actingAs($admin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit')),
        'pages::settings.security',
    );

    $this->withSession(['auth.password_confirmed_at' => $confirmed ? time() : time() - config('auth.password_timeout') - 1]);

    $response = $this->postJson(Livewire::getUpdateUri(), accessBoundaryPayload(
        $snapshot, 'disable',
    ), ['X-Livewire' => 'true']);

    if ($confirmed) {
        $response->assertOk();
        expect($admin->fresh()->two_factor_secret)->toBeNull();
    } else {
        $response->assertStatus(423);
        expect($admin->fresh()->two_factor_secret)->not->toBeNull();
    }
})->with([true, false])->with([60, 10800]);

test('private Livewire pages recheck access before rendering fresh data', function (string $routeName, string $access) {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->unpublished()->create();
    $skill = Skill::factory()->create();
    $snapshot = accessBoundarySnapshot(
        $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()])
            ->get(route($routeName, ['project' => $project, 'skill' => $skill])),
    );

    if ($access === 'revoked') {
        $admin->forceFill(['is_admin' => false])->save();
    } elseif ($access === 'unverified') {
        $admin->forceFill(['email_verified_at' => null])->save();
    }

    $this->actingAs($admin->fresh());
    $privateMessage = 'Inquiry created after the original page load';
    Inquiry::factory()->create(['message' => $privateMessage]);

    $response = $this->postJson(Livewire::getUpdateUri(), accessBoundaryPayload($snapshot, '$refresh'), ['X-Livewire' => 'true']);

    if ($access === 'permitted') {
        $response->assertOk();

        if ($routeName === 'admin.inquiries.index') {
            expect($response->json('components.0.effects.html'))->toContain($privateMessage);
        }
    } else {
        $response->assertForbidden()->assertDontSee($privateMessage);
    }
})->with([
    'dashboard', 'admin.projects.index', 'admin.projects.create', 'admin.projects.edit',
    'admin.skills.index', 'admin.skills.create', 'admin.skills.edit', 'admin.inquiries.index',
    'admin.settings.edit', 'profile.edit', 'appearance.edit', 'security.edit',
])->with(['permitted', 'revoked', 'unverified']);

test('nested recovery code actions recheck password confirmation', function (bool $confirmed) {
    $admin = User::factory()->admin()->withTwoFactor()->create();
    $originalCodes = $admin->two_factor_recovery_codes;
    $snapshot = accessBoundarySnapshot(
        $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()])->get(route('security.edit')),
        'pages::settings.two-factor.recovery-codes',
    );

    $this->withSession(['auth.password_confirmed_at' => $confirmed ? time() : 0]);
    $response = $this->postJson(Livewire::getUpdateUri(), accessBoundaryPayload($snapshot, 'regenerateRecoveryCodes'), ['X-Livewire' => 'true']);

    if ($confirmed) {
        $response->assertOk();
        expect($admin->fresh()->two_factor_recovery_codes)->not->toBe($originalCodes);
    } else {
        $response->assertStatus(423);
        expect($admin->fresh()->two_factor_recovery_codes)->toBe($originalCodes);
        $response->assertDontSee('recovery-code-1');
    }
})->with([true, false]);

test('an expired HTML Livewire request redirects to password confirmation without changing two factor state', function () {
    $admin = User::factory()->admin()->withTwoFactor()->create();
    $snapshot = accessBoundarySnapshot(
        $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()])->get(route('security.edit')),
        'pages::settings.security',
    );

    $this->withSession(['auth.password_confirmed_at' => 0]);
    $this->postJson(Livewire::getUpdateUri(), accessBoundaryPayload($snapshot, 'disable'), ['X-Livewire' => 'true', 'Accept' => 'text/html'])
        ->assertRedirect(route('password.confirm'));

    expect($admin->fresh()->two_factor_secret)->not->toBeNull();
});

test('signed component snapshots cannot be changed to bypass route permissions', function () {
    $admin = User::factory()->admin()->create();
    $inquiry = Inquiry::factory()->create(['status' => 'new']);
    $snapshot = json_decode(accessBoundarySnapshot($this->actingAs($admin)->get(route('admin.inquiries.index'))), true);
    $snapshot['memo']['path'] = '/';

    $this->withoutExceptionHandling();

    expect(fn () => $this->postJson(Livewire::getUpdateUri(), accessBoundaryPayload(
        json_encode($snapshot), 'markStatus', [$inquiry->id, 'contacted'],
    ), ['X-Livewire' => 'true']))->toThrow(CorruptComponentPayloadException::class);

    expect($inquiry->fresh()->status)->toBe('new');
});

test('a logged out user cannot reuse a private component snapshot', function () {
    $admin = User::factory()->admin()->create();
    $inquiry = Inquiry::factory()->create(['status' => 'new']);
    $snapshot = accessBoundarySnapshot($this->actingAs($admin)->get(route('admin.inquiries.index')));

    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->postJson(Livewire::getUpdateUri(), accessBoundaryPayload(
        $snapshot, 'markStatus', [$inquiry->id, 'contacted'],
    ), ['X-Livewire' => 'true'])->assertUnauthorized()->assertDontSee($inquiry->message);

    expect($inquiry->fresh()->status)->toBe('new');
});
