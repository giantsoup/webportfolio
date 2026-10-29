<?php

use App\Models\Project;

test('work index numbers rows across pages and provides accessible pagination', function () {
    Project::query()->delete();
    $projects = Project::factory()->count(10)->sequence(fn ($sequence) => [
        'title' => 'Published project '.($sequence->index + 1),
        'sort_order' => $sequence->index,
    ])->create();
    $draft = Project::factory()->unpublished()->create(['title' => 'Private draft']);

    $firstPage = $this->get(route('projects.index'))
        ->assertSuccessful()
        ->assertSee('aria-label="Pagination"', false)
        ->assertSee('aria-current="page" aria-label="Page 1"', false)
        ->assertSee('rel="next"', false)
        ->assertSee('Published project 9')
        ->assertDontSee('Published project 10')
        ->assertDontSee($draft->title);

    $document = new DOMDocument;
    @$document->loadHTML($firstPage->getContent());
    $xpath = new DOMXPath($document);
    $numbers = $xpath->query('//main//ol/li/span[not(@aria-hidden)]');

    expect(array_map(fn (DOMNode $node): string => trim($node->textContent), iterator_to_array($numbers)))
        ->toBe(['01', '02', '03', '04', '05', '06', '07', '08', '09']);

    $this->get(route('projects.index', ['page' => 2]))
        ->assertSuccessful()
        ->assertSee('start="10"', false)
        ->assertSeeText('10')
        ->assertSee($projects->last()->title)
        ->assertDontSee('href="'.route('projects.show', $projects->first()).'"', false)
        ->assertSee('aria-current="page" aria-label="Page 2"', false)
        ->assertSee('rel="prev"', false);
});

test('empty work index offers neutral copy without pagination or admin instructions', function () {
    Project::query()->delete();

    $this->get(route('projects.index'))
        ->assertSuccessful()
        ->assertSee('New work is being written up. Check back soon.')
        ->assertDontSee('aria-label="Pagination"', false)
        ->assertDontSee('from the admin');
});

test('project body renders separate escaped paragraphs', function () {
    $project = Project::factory()->create(['body' => "First paragraph.\n\n<script>alert('unsafe')</script>"]);
    $response = $this->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertSee('&lt;script&gt;', false)
        ->assertDontSee("<script>alert('unsafe')</script>", false);
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);

    expect($xpath->query('//*[@data-project-body]/p'))->toHaveCount(2)
        ->and($project->body_paragraphs)->toBe(['First paragraph.', "<script>alert('unsafe')</script>"]);
});

test('project paragraphs handle empty bodies and mixed line endings', function (string $body, array $paragraphs) {
    expect((new Project(['body' => $body]))->body_paragraphs)->toBe($paragraphs);
})->with([
    'empty' => ['', []],
    'whitespace' => [" \n\n ", []],
    'windows' => ["First\r\n\r\nSecond", ['First', 'Second']],
    'single line break' => ["First\nsecond", ["First\nsecond"]],
]);

test('project links and images render only when supplied', function (bool $hasLinks) {
    $project = Project::factory()->create([
        'live_url' => $hasLinks ? 'https://example.com/live' : null,
        'repo_url' => $hasLinks ? 'https://example.com/source' : null,
        'case_study_url' => $hasLinks ? 'https://example.com/case' : null,
        'featured_image_path' => $hasLinks ? 'projects/sample.png' : null,
    ]);
    $response = $this->get(route('projects.show', $project))->assertSuccessful();
    foreach (['live', 'source', 'case'] as $path) {
        if ($hasLinks) {
            $response->assertSee('href="https://example.com/'.$path.'"', false);
        } else {
            $response->assertDontSee('href="https://example.com/'.$path.'"', false);
        }
    }
    if ($hasLinks) {
        $response->assertSee('loading="lazy"', false)->assertSee('storage/projects/sample.png', false);
    } else {
        $response->assertDontSee('<img', false);
    }
})->with([true, false]);

test('related work excludes the current project and unpublished content', function () {
    Project::query()->delete();
    $project = Project::factory()->create();
    $related = Project::factory()->create();
    $draft = Project::factory()->unpublished()->create();

    $this->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertSee('More from the index')
        ->assertSee('href="'.route('projects.show', $related).'"', false)
        ->assertDontSee('href="'.route('projects.show', $project).'"', false)
        ->assertDontSee('href="'.route('projects.show', $draft).'"', false);
});

test('unpublished and future dated projects return not found', function () {
    $draft = Project::factory()->unpublished()->create();
    $future = Project::factory()->create(['published_at' => now()->addDay()]);

    $this->get(route('projects.show', $draft))->assertNotFound();
    $this->get(route('projects.show', $future))->assertNotFound();
});
