<?php

namespace App\Services\GitHub;

final readonly class GitHubRepositoryDetails
{
    /**
     * @param  array<string, int>  $languages  Languages keyed by name with byte counts, largest first.
     */
    public function __construct(
        public array $languages,
        public ?string $readme,
    ) {}
}
