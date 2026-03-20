<?php

test('contact form is throttled after repeated submissions from the same ip', function () {
    config(['portfolio.contact_recipient_address' => null]);

    $payload = [
        'name' => 'Taylor',
        'email' => 'taylor@example.com',
        'message' => str_repeat('Detailed message ', 4),
    ];

    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.store'), $payload)->assertRedirect(route('contact.create'));
    }

    $this->post(route('contact.store'), $payload)->assertTooManyRequests();
});
