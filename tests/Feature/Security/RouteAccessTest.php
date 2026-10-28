<?php

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

dataset('private portfolio routes', [
    'dashboard' => ['dashboard'],
    'projects' => ['admin.projects.index'],
    'create project' => ['admin.projects.create'],
    'edit project' => ['admin.projects.edit'],
    'skills' => ['admin.skills.index'],
    'create skill' => ['admin.skills.create'],
    'edit skill' => ['admin.skills.edit'],
    'inquiries' => ['admin.inquiries.index'],
    'portfolio settings' => ['admin.settings.edit'],
    'profile' => ['profile.edit'],
    'appearance' => ['appearance.edit'],
    'security' => ['security.edit'],
]);

test('private portfolio routes enforce access on GET and HEAD', function (string $routeName, string $method, string $actor) {
    $project = Project::factory()->unpublished()->create(['title' => 'Private audit draft']);
    $skill = Skill::factory()->create();
    $inquiry = Inquiry::factory()->create(['message' => 'Private audit inquiry message']);

    if ($actor !== 'guest') {
        $user = User::factory()->create([
            'is_admin' => $actor !== 'non-admin',
            'email_verified_at' => $actor === 'unverified' ? null : now(),
        ]);
        $this->actingAs($user);
    }

    $projectCount = Project::query()->count();

    $response = $this->withSession(['auth.password_confirmed_at' => time()])
        ->call($method, route($routeName, ['project' => $project, 'skill' => $skill]));

    if ($actor === 'admin') {
        $response->assertOk();
    } elseif ($actor === 'guest') {
        $response->assertRedirect(route('login'));
    } elseif ($actor === 'unverified') {
        $response->assertRedirect(route('verification.notice'));
    } else {
        $response->assertForbidden();
    }

    if ($actor !== 'admin') {
        $response->assertDontSee($inquiry->message)->assertDontSee($project->title);
    }

    $this->assertDatabaseCount('projects', $projectCount);
    $this->assertDatabaseCount('inquiries', 1);
})->with('private portfolio routes')->with(['GET', 'HEAD'])->with(['guest', 'non-admin', 'unverified', 'admin']);

test('settings redirect enforces access for every registered method', function (string $method, string $actor) {
    if ($actor !== 'guest') {
        $this->actingAs(User::factory()->create([
            'is_admin' => $actor !== 'non-admin',
            'email_verified_at' => $actor === 'unverified' ? null : now(),
        ]));
    }

    $response = $this->call($method, '/settings');

    if ($actor === 'guest') {
        $response->assertRedirect(route('login'));
    } elseif ($actor === 'non-admin') {
        $response->assertForbidden();
    } elseif ($actor === 'unverified') {
        $response->assertRedirect(route('verification.notice'));
    } else {
        $response->assertRedirect('/settings/profile');
    }
})->with(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'QUERY'])
    ->with(['guest', 'non-admin', 'unverified', 'admin']);
