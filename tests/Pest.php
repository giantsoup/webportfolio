<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Build a GitHub REST API repository payload for the configured test account.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function githubRepositoryPayload(string $name, array $overrides = []): array
{
    return [
        'full_name' => "giantsoup/{$name}",
        'name' => $name,
        'description' => "The {$name} repository.",
        'html_url' => "https://github.com/giantsoup/{$name}",
        'homepage' => null,
        'language' => 'PHP',
        'topics' => [],
        'stargazers_count' => 3,
        'forks_count' => 1,
        'fork' => false,
        'archived' => false,
        'pushed_at' => '2026-10-01T12:00:00Z',
        ...$overrides,
    ];
}

/**
 * Point the GitHub client at a test account and fake its repository listing, languages and README.
 *
 * @param  list<array<string, mixed>>  $repositories
 * @param  array<string, int>  $languages
 */
function fakeGitHub(array $repositories, array $languages = ['PHP' => 1000], ?string $readme = null): void
{
    config(['services.github.username' => 'giantsoup', 'services.github.token' => 'test-token']);

    Http::fake([
        'api.github.com/users/giantsoup/repos*' => Http::response($repositories),
        'api.github.com/repos/*/languages' => Http::response($languages),
        'api.github.com/repos/*/readme' => $readme === null ? Http::response(['message' => 'Not Found'], 404) : Http::response($readme),
    ]);
}
