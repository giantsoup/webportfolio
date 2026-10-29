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
