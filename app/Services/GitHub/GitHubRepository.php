<?php

namespace App\Services\GitHub;

use Carbon\CarbonImmutable;

final readonly class GitHubRepository
{
    /**
     * @param  list<string>  $topics
     */
    public function __construct(
        public string $fullName,
        public string $name,
        public ?string $description,
        public string $htmlUrl,
        public ?string $homepage,
        public ?string $language,
        public array $topics,
        public int $stars,
        public int $forks,
        public bool $isFork,
        public bool $isArchived,
        public ?CarbonImmutable $pushedAt,
    ) {}

    /**
     * Build a repository from a GitHub REST API repository payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function fromApi(array $payload): self
    {
        return new self(
            fullName: (string) $payload['full_name'],
            name: (string) $payload['name'],
            description: filled($payload['description'] ?? null) ? (string) $payload['description'] : null,
            htmlUrl: (string) $payload['html_url'],
            homepage: filter_var($payload['homepage'] ?? null, FILTER_VALIDATE_URL) ? (string) $payload['homepage'] : null,
            language: filled($payload['language'] ?? null) ? (string) $payload['language'] : null,
            topics: array_values(array_map('strval', $payload['topics'] ?? [])),
            stars: (int) ($payload['stargazers_count'] ?? 0),
            forks: (int) ($payload['forks_count'] ?? 0),
            isFork: (bool) ($payload['fork'] ?? false),
            isArchived: (bool) ($payload['archived'] ?? false),
            pushedAt: filled($payload['pushed_at'] ?? null) ? CarbonImmutable::parse($payload['pushed_at']) : null,
        );
    }
}
