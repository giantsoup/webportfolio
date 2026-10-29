<?php

test('public head keeps the portfolio favicons and self hosts its display fonts', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('fonts.googleapis.com', false)
        ->assertSee('href="/favicon.ico"', false)
        ->assertSee('href="/favicon-32x32.png"', false)
        ->assertSee('href="/favicon-16x16.png"', false)
        ->assertSee('href="/apple-touch-icon.png"', false)
        ->assertDontSee('href="/favicon.svg"', false);
});
