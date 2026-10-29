<?php

use App\Models\PortfolioSetting;
use App\Models\Project;

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

test('public navigation identifies the current page and provides a skip link', function (string $route, string $label) {
    $parameters = $route === 'projects.show' ? Project::factory()->create() : [];
    $response = $this->get(route($route, $parameters))->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);

    expect($xpath->query('//body//a')->item(0)->getAttribute('href'))->toBe('#main')
        ->and($xpath->query('//*[@id="main"]'))->toHaveCount(1)
        ->and(trim($xpath->query('//nav[@aria-label="Primary"]//a[@aria-current="page"]')->item(0)->textContent))->toBe($label)
        ->and($xpath->query('//a[@aria-label="Taylor Portfolio, home"]//svg'))->toHaveCount(1)
        ->and($xpath->query('//meta[@name="description"]'))->toHaveCount(1);
})->with([
    ['home', 'Home'],
    ['projects.index', 'Work'],
    ['projects.show', 'Work'],
    ['contact.create', 'Contact'],
]);

test('footer social links render only for filled settings', function (bool $hasLinks) {
    PortfolioSetting::factory()->create([
        'github_url' => $hasLinks ? 'https://github.com/portfolio-review' : null,
        'linkedin_url' => $hasLinks ? 'https://linkedin.com/in/portfolio-review' : null,
        'x_url' => $hasLinks ? 'https://x.com/portfolio-review' : null,
    ]);
    $response = $this->get(route('home'))->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);

    expect($xpath->query('//footer//a[@target="_blank" and @rel="noreferrer"]'))->toHaveCount($hasLinks ? 3 : 0);
})->with([true, false]);

test('public frame retires the neon overlays and public Livewire scripts', function () {
    $this->get(route('home'))->assertSuccessful()
        ->assertDontSee('portfolio-grid')
        ->assertDontSee('portfolio-glow')
        ->assertDontSee('backdrop-blur')
        ->assertDontSee('livewire/livewire.js', false);
});
