<?php

use App\Mail\PortfolioInquiryReceived;
use Illuminate\Support\Facades\Mail;

test('contact form persists inquiries and queues mail', function () {
    Mail::fake();
    config(['portfolio.contact_recipient_address' => 'owner@example.com']);

    $response = $this->post(route('contact.store'), [
        'name' => 'Taylor',
        'email' => 'taylor@example.com',
        'company' => 'Acme',
        'project_type' => 'Architecture consulting',
        'message' => str_repeat('Detailed message ', 4),
    ]);

    $response->assertRedirect(route('contact.create'));

    $this->assertDatabaseHas('inquiries', [
        'email' => 'taylor@example.com',
        'status' => 'new',
    ]);

    Mail::assertQueued(PortfolioInquiryReceived::class);
});

test('contact form validates required fields', function () {
    $response = $this->post(route('contact.store'), [
        'name' => '',
        'email' => 'invalid-email',
        'message' => 'short',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'message']);
});

test('contact fields expose matching labels and validation attributes', function () {
    $response = $this->get(route('contact.create'))->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);

    foreach (['name', 'email', 'company', 'project_type', 'message'] as $field) {
        $input = $xpath->query('//*[@id="'.$field.'"]')->item(0);
        expect($xpath->query('//label[@for="'.$field.'"]'))->toHaveCount(1)
            ->and($input->getAttribute('name'))->toBe($field)
            ->and($input->getAttribute('maxlength'))->toBe($field === 'message' ? '5000' : '255')
            ->and($input->hasAttribute('required'))->toBe(in_array($field, ['name', 'email', 'message']));
    }
    expect($xpath->query('//*[@id="message"]')->item(0)->getAttribute('minlength'))->toBe('20')
        ->and($xpath->query('//*[@id="message"]')->item(0)->getAttribute('aria-describedby'))->toBe('message-hint')
        ->and($xpath->query('//*[@id="email"]')->item(0)->getAttribute('type'))->toBe('email');
    foreach (['name' => 'name', 'email' => 'email', 'company' => 'organization'] as $field => $autocomplete) {
        expect($xpath->query('//*[@id="'.$field.'"]')->item(0)->getAttribute('autocomplete'))->toBe($autocomplete);
    }
});

test('invalid submissions expose associated errors and preserve old input', function () {
    $response = $this->from(route('contact.create'))->followingRedirects()
        ->post(route('contact.store'), [
            'name' => '',
            'email' => 'invalid-email',
            'company' => 'Preserved company',
            'message' => 'short',
        ])
        ->assertSuccessful()
        ->assertSee('role="alert"', false)
        ->assertSee('Please include a bit more detail so I can respond usefully.')
        ->assertSee('value="Preserved company"', false);
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    foreach (['name', 'email', 'message'] as $field) {
        $input = $xpath->query('//*[@id="'.$field.'"]')->item(0);
        expect($input->getAttribute('aria-invalid'))->toBe('true')
            ->and($input->getAttribute('aria-describedby'))->toContain($field.'-error')
            ->and($xpath->query('//*[@id="'.$field.'-error"]'))->toHaveCount(1);
    }
    expect($xpath->query('//*[@id="message"]')->item(0)->getAttribute('aria-describedby'))->toContain('message-hint');
});

test('valid submission displays the success status after redirect', function () {
    Mail::fake();
    $this->followingRedirects()->post(route('contact.store'), [
        'name' => 'Portfolio review',
        'email' => 'review@example.com',
        'message' => 'A detailed test message about the portfolio contact form.',
    ])->assertSuccessful()
        ->assertSee('role="status"', false)
        ->assertSee('Thanks for reaching out. Your message is in the queue.');
});
