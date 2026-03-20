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
