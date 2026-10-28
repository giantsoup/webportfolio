<?php

use App\Models\Inquiry;
use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Http::preventStrayRequests();
    Mail::fake();
});

test('public project routes exclude private and future projects on GET and HEAD', function (string $method) {
    $published = Project::factory()->featured()->create(['title' => 'Published audit project', 'sort_order' => 0, 'published_at' => now()->subDay()]);
    $draft = Project::factory()->unpublished()->create(['title' => 'Private audit project']);
    $future = Project::factory()->create(['title' => 'Future audit project', 'published_at' => now()->addDay()]);

    foreach (['home', 'projects.index'] as $name) {
        $response = $this->call($method, route($name))->assertOk();

        if ($method === 'GET') {
            $response->assertSee($published->title)->assertDontSee($draft->title)->assertDontSee($future->title);
        }
    }

    $this->call($method, route('projects.show', $published))->assertOk();
    $this->call($method, route('projects.show', $draft))->assertNotFound();
    $this->call($method, route('projects.show', $future))->assertNotFound();
})->with(['GET', 'HEAD']);

test('public project output escapes stored markup', function () {
    $markup = '<script>alert("audit")</script>';
    $project = Project::factory()->create(['body' => $markup]);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee($markup)
        ->assertDontSee($markup, false);
});

test('contact writes require a CSRF token and ignore privileged fields', function () {
    $this->app->instance('env', 'local');
    $payload = [
        'name' => 'Audit Sender',
        'email' => 'sender@example.test',
        'message' => 'A synthetic inquiry with enough detail for validation.',
        'status' => 'contacted',
        'contacted_at' => now()->toDateTimeString(),
        'ip_address' => '203.0.113.99',
    ];

    $this->withSession(['_token' => 'audit-csrf-token'])
        ->post(route('contact.store'), $payload)->assertStatus(419);

    $this->assertDatabaseCount('inquiries', 0);
    Mail::assertNothingQueued();

    $this->post(route('contact.store'), [...$payload, '_token' => 'audit-csrf-token'])
        ->assertRedirect(route('contact.create'));

    $inquiry = Inquiry::query()->sole();
    expect($inquiry->status)->toBe('new');
    expect($inquiry->contacted_at)->toBeNull();
    expect($inquiry->ip_address)->not->toBe($payload['ip_address']);
});

test('private local storage requires an unexpired object signature', function () {
    Storage::fake('local');
    Storage::disk('local')->put('audit/private.txt', 'Synthetic private object');

    $this->get('/storage/audit/private.txt')->assertForbidden()->assertDontSee('Synthetic private object');
    $this->call('PUT', '/storage/audit/unauthorized.txt', content: 'Unauthorized write')->assertForbidden();
    Storage::disk('local')->assertMissing('audit/unauthorized.txt');

    $signedUrl = URL::temporarySignedRoute('storage.local', now()->addMinute(), ['path' => 'audit/private.txt'], absolute: false);
    $this->get($signedUrl)->assertOk()->assertStreamedContent('Synthetic private object');

    $uploadUrl = URL::temporarySignedRoute('storage.local.upload', now()->addMinute(), ['path' => 'audit/uploaded.txt', 'upload' => true], absolute: false);
    $this->call('PUT', $uploadUrl, content: 'Synthetic allowed upload')->assertNoContent();
    expect(Storage::disk('local')->get('audit/uploaded.txt'))->toBe('Synthetic allowed upload');

    $this->travel(2)->minutes();
    $this->get($signedUrl)->assertForbidden()->assertDontSee('Synthetic private object');
});

test('temporary upload and preview routes reject unsigned requests', function () {
    Storage::fake('local');

    $this->post(route('livewire.upload-file'))->assertUnauthorized();
    $this->get(route('livewire.preview-file', ['filename' => 'audit.png']))->assertUnauthorized();

    Storage::disk('local')->assertDirectoryEmpty('/');
});
