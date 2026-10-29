<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\GitHub\GitHubClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\HttpClientException;

#[Signature('app:sync-github-projects')]
#[Description('Refresh GitHub stats for linked projects and link projects whose repository URL points at GitHub')]
class SyncGitHubProjectsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GitHubClient $github): int
    {
        if (! $github->isConfigured()) {
            $this->components->warn('GITHUB_USERNAME is not set, so there is nothing to sync.');

            return self::SUCCESS;
        }

        $github->refresh();

        try {
            $github->repositories();
        } catch (HttpClientException $exception) {
            $this->components->error("GitHub could not be reached: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $matches = collect();
        $missing = [];
        $claimed = Project::query()->whereNotNull('github_repository')->pluck('github_repository')->map(fn (string $fullName): string => strtolower($fullName));

        Project::query()
            ->whereNotNull('github_repository')
            ->orWhereNotNull('repo_url')
            ->orderBy('id')
            ->each(function (Project $project) use ($github, $claimed, $matches, &$missing): void {
                $fullName = $project->github_repository ?? Project::gitHubRepositoryFromUrl($project->repo_url);

                if ($fullName === null) {
                    return;
                }

                $repository = $github->repository($fullName);

                if (! $repository) {
                    if ($project->github_repository !== null) {
                        $missing[] = $project->github_repository;
                    }

                    return;
                }

                if ($project->github_repository === null) {
                    if ($claimed->contains(strtolower($repository->fullName))) {
                        return;
                    }

                    $claimed->push(strtolower($repository->fullName));
                }

                $matches->push(['project' => $project, 'repository' => $repository]);
            });

        try {
            $details = $github->detailsFor($matches->pluck('repository'));
        } catch (HttpClientException $exception) {
            $this->components->error("GitHub could not be reached: {$exception->getMessage()}");

            return self::FAILURE;
        }

        foreach ($matches as $match) {
            $match['project']->syncFromGitHub($match['repository'], $details[$match['repository']->fullName]);
        }

        $synced = $matches->count();

        $this->components->info("Synced {$synced} project(s) from GitHub.");

        foreach ($missing as $fullName) {
            $this->components->warn("{$fullName} is no longer a public repository of {$github->username()}.");
        }

        return self::SUCCESS;
    }
}
