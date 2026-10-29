<?php

use App\Models\PortfolioSetting;
use App\Models\Project;
use App\Models\Skill;

test('public portfolio pages render published content', function () {
    PortfolioSetting::factory()->create();

    $skills = Skill::factory()->count(2)->create();
    $publishedProject = Project::factory()->featured()->create([
        'title' => 'Telemetry Hub',
    ]);
    $publishedProject->skills()->sync($skills->pluck('id'));

    $draftProject = Project::factory()->unpublished()->create([
        'title' => 'Hidden Draft',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('aria-label="Taylor Portfolio, home"', false)
        ->assertSee('href="/favicon.ico"', false)
        ->assertSee('href="/favicon-32x32.png"', false)
        ->assertSee('href="/favicon-16x16.png"', false)
        ->assertSee('href="/apple-touch-icon.png"', false)
        ->assertDontSee('href="/favicon.svg"', false)
        ->assertSee('Focused on long-term product systems')
        ->assertSee('Laravel architecture, operational software, and durable internal tools.')
        ->assertSee('Housing Compliance Platform')
        ->assertSee('Since 2020, I have served as the sole in-house developer')
        ->assertSee('Sole In-House Developer, 2020')
        ->assertSee('I own the long-term technical direction and daily engineering execution')
        ->assertSee('More than feature delivery.')
        ->assertSee('CDLAC')
        ->assertSee('Core systems I delivered')
        ->assertSee('Compliance workflows')
        ->assertSee('Modernization efforts')
        ->assertSee('Architecture cleanup')
        ->assertSee('Telemetry Hub')
        ->assertDontSee('Hidden Draft');

    $this->get(route('projects.index'))
        ->assertOk()
        ->assertSee('Telemetry Hub')
        ->assertDontSee('Hidden Draft');

    $this->get(route('projects.show', $publishedProject))
        ->assertOk()
        ->assertSee('Telemetry Hub');

    $this->get(route('contact.create'))
        ->assertOk()
        ->assertSee('Get in')
        ->assertSee('Focused on long-term product systems')
        ->assertSee('Reach out for relevant introductions, product conversations, or thoughtful professional inquiries.')
        ->assertDontSee('Available for new opportunities');

    $this->get(route('projects.show', $draftProject))
        ->assertNotFound();
});

test('home presents the cover story and links project rows to their details', function () {
    $project = Project::factory()->featured()->create();

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSeeText('Five years, one codebase.')
        ->assertSeeText('Built, then rebuilt better.')
        ->assertSeeText('The index')
        ->assertSeeText('Write in.')
        ->assertSee('id="feature"', false)
        ->assertSee('id="built"', false)
        ->assertSee('id="work"', false)
        ->assertSee('href="'.route('projects.show', $project).'"', false);
});

test('empty home hides the work and stack sections without admin instructions', function () {
    Project::query()->delete();
    Skill::query()->delete();

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('id="work"', false)
        ->assertDontSee('id="stack-title"', false)
        ->assertDontSee('Publish a few projects from the admin')
        ->assertDontSee('Add featured skills from the admin');
});

test('empty home contents links all point to available destinations', function () {
    Project::query()->delete();
    $response = $this->get(route('home'))->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);

    foreach ($xpath->query('//a[starts-with(@href, "#")]') as $link) {
        $id = substr($link->getAttribute('href'), 1);
        expect($xpath->query('//*[@id="'.$id.'"]'))->toHaveCount(1);
    }
    expect($xpath->query('//aside[@aria-labelledby="toc-title"]//a[@href="'.route('projects.index').'"]')->length)->toBeGreaterThan(0);
});
