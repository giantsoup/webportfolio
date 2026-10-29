<?php

namespace App\Services\GitHub;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GitHubClient
{
    /**
     * The maximum number of 100-repository pages fetched for one listing.
     */
    private const int MAX_PAGES = 10;

    /**
     * How many repositories have their details fetched at once.
     */
    private const int DETAIL_BATCH_SIZE = 10;

    public function __construct(
        private ?string $username,
        private ?string $token = null,
    ) {}

    /**
     * Determine whether a GitHub account is configured.
     */
    public function isConfigured(): bool
    {
        return filled($this->username);
    }

    /**
     * Get the configured account name.
     */
    public function username(): ?string
    {
        return $this->username;
    }

    /**
     * Get the public repositories owned by the configured account, most recently pushed first.
     *
     * @return Collection<int, GitHubRepository>
     */
    public function repositories(): Collection
    {
        if (! $this->isConfigured()) {
            return collect();
        }

        $payloads = Cache::remember($this->cacheKey(), now()->addMinutes(10), function (): array {
            $repositories = [];

            for ($page = 1; $page <= self::MAX_PAGES; $page++) {
                $batch = $this->request()
                    ->get("users/{$this->username}/repos", [
                        'type' => 'owner',
                        'sort' => 'pushed',
                        'per_page' => 100,
                        'page' => $page,
                    ])
                    ->throw()
                    ->json();

                $repositories = [...$repositories, ...$batch];

                if (count($batch) < 100) {
                    break;
                }
            }

            return $repositories;
        });

        return collect($payloads)->map(fn (array $payload): GitHubRepository => GitHubRepository::fromApi($payload));
    }

    /**
     * Find one of the configured account's public repositories by its "owner/name".
     */
    public function repository(string $fullName): ?GitHubRepository
    {
        return $this->repositories()->first(
            fn (GitHubRepository $repository): bool => strcasecmp($repository->fullName, $fullName) === 0,
        );
    }

    /**
     * Get the languages and README of one repository.
     */
    public function details(GitHubRepository $repository): GitHubRepositoryDetails
    {
        return $this->detailsFor(collect([$repository]))[$repository->fullName];
    }

    /**
     * Get the languages and README of several repositories, fetched in parallel batches.
     *
     * @param  Collection<int, GitHubRepository>  $repositories
     * @return array<string, GitHubRepositoryDetails> Keyed by the repository's full name.
     */
    public function detailsFor(Collection $repositories): array
    {
        $details = [];

        foreach ($repositories->chunk(self::DETAIL_BATCH_SIZE) as $batch) {
            $responses = Http::pool(fn (Pool $pool): array => $batch->flatMap(fn (GitHubRepository $repository): array => [
                $this->configure($pool->as("{$repository->fullName}:languages"))
                    ->get("repos/{$repository->fullName}/languages"),
                $this->configure($pool->as("{$repository->fullName}:readme"))
                    ->accept('application/vnd.github.raw+json')
                    ->get("repos/{$repository->fullName}/readme"),
            ])->all());

            foreach ($batch as $repository) {
                $languages = $this->successfulResponse($responses["{$repository->fullName}:languages"]);
                $readme = $this->successfulResponse($responses["{$repository->fullName}:readme"], allowMissing: true);

                $details[$repository->fullName] = new GitHubRepositoryDetails(
                    languages: $languages?->json() ?? [],
                    readme: filled($readme?->body()) ? $readme->body() : null,
                );
            }
        }

        return $details;
    }

    /**
     * Forget the cached repository listing so the next call fetches it again.
     */
    public function refresh(): void
    {
        Cache::forget($this->cacheKey());
    }

    private function request(): PendingRequest
    {
        return $this->configure(Http::createPendingRequest())
            ->retry(2, 250, fn (Throwable $exception): bool => $exception instanceof ConnectionException
                || ($exception instanceof RequestException && $exception->response->serverError()), throw: false);
    }

    private function configure(PendingRequest $request): PendingRequest
    {
        return $request
            ->baseUrl('https://api.github.com')
            ->accept('application/vnd.github+json')
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->when(filled($this->token), fn (PendingRequest $request) => $request->withToken($this->token))
            ->timeout(10);
    }

    /**
     * Return a pooled response when it succeeded, null for an allowed 404, and throw for anything else.
     *
     * @throws Throwable
     */
    private function successfulResponse(Response|Throwable $response, bool $allowMissing = false): ?Response
    {
        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($allowMissing && $response->notFound()) {
            return null;
        }

        return $response->throw();
    }

    private function cacheKey(): string
    {
        return 'github.repositories.'.strtolower((string) $this->username);
    }
}
