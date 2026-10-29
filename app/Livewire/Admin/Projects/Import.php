<?php

namespace App\Livewire\Admin\Projects;

use App\Models\Project;
use App\Services\GitHub\GitHubClient;
use App\Services\GitHub\GitHubProjectImporter;
use App\Services\GitHub\GitHubRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Import from GitHub')]
class Import extends Component
{
    #[Url(except: '')]
    public string $search = '';

    #[Url(except: false)]
    public bool $showForks = false;

    #[Url(except: false)]
    public bool $showArchived = false;

    /**
     * Fetch a fresh repository listing from GitHub.
     */
    public function refresh(GitHubClient $github): void
    {
        $github->refresh();
    }

    /**
     * Link an existing project whose repository URL points at the given repository.
     */
    public function link(GitHubClient $github, string $fullName): void
    {
        try {
            $repository = $github->repository($fullName);
            $project = $repository ? $this->unlinkedProjectsByRepository()->get(Str::lower($repository->fullName)) : null;

            if (! $project) {
                return;
            }

            $project->syncFromGitHub($repository, $github->details($repository));
        } catch (HttpClientException) {
            session()->flash('github_error', __('GitHub could not be reached. Try again in a moment.'));

            return;
        }

        session()->flash('status', __(':title is now linked to :repository.', [
            'title' => $project->title,
            'repository' => $repository->fullName,
        ]));
    }

    /**
     * Import every repository in the current filtered list that is not linked yet: matching projects
     * are linked and the rest become unpublished drafts.
     */
    public function importAll(GitHubClient $github, GitHubProjectImporter $importer): void
    {
        try {
            $result = $importer->importAll($this->filteredRepositories($github->repositories()));
        } catch (HttpClientException) {
            session()->flash('github_error', __('GitHub could not be reached. Try again in a moment.'));

            return;
        }

        session()->flash('status', __('Created :created draft project(s) and linked :linked existing project(s).', $result));
    }

    /**
     * Apply the search and fork/archive filters to a repository listing.
     *
     * @param  Collection<int, GitHubRepository>  $repositories
     * @return Collection<int, GitHubRepository>
     */
    private function filteredRepositories(Collection $repositories): Collection
    {
        $search = Str::lower(trim($this->search));

        return $repositories
            ->reject(fn (GitHubRepository $repository): bool => (! $this->showForks && $repository->isFork)
                || (! $this->showArchived && $repository->isArchived))
            ->filter(fn (GitHubRepository $repository): bool => $search === ''
                || Str::contains(Str::lower(implode(' ', [$repository->name, $repository->description, $repository->language, ...$repository->topics])), $search))
            ->values();
    }

    /**
     * Get unlinked projects whose repository URL points at a GitHub repository, keyed by lowercase "owner/name".
     *
     * @return Collection<string, Project>
     */
    private function unlinkedProjectsByRepository(): Collection
    {
        return Project::query()
            ->whereNull('github_repository')
            ->whereNotNull('repo_url')
            ->get(['id', 'title', 'slug', 'repo_url'])
            ->toBase()
            ->keyBy(fn (Project $project): string => Str::lower((string) Project::gitHubRepositoryFromUrl($project->repo_url)))
            ->except('');
    }

    public function render(): View
    {
        $github = app(GitHubClient::class);
        $repositories = collect();
        $failed = false;

        try {
            $repositories = $github->repositories();
        } catch (HttpClientException) {
            $failed = true;
        }

        $linkedProjects = Project::query()
            ->whereNotNull('github_repository')
            ->get(['id', 'title', 'slug', 'github_repository'])
            ->toBase()
            ->keyBy(fn (Project $project): string => Str::lower($project->github_repository));
        $visibleRepositories = $this->filteredRepositories($repositories);

        return view('livewire.admin.projects.import', [
            'githubConfigured' => $github->isConfigured(),
            'githubUsername' => $github->username(),
            'failed' => $failed,
            'totalCount' => $repositories->count(),
            'repositories' => $visibleRepositories,
            'pendingCount' => $visibleRepositories
                ->reject(fn (GitHubRepository $repository): bool => $linkedProjects->has(Str::lower($repository->fullName)))
                ->count(),
            'linkedProjects' => $linkedProjects,
            'linkableProjects' => $this->unlinkedProjectsByRepository(),
        ])->layout('layouts.app');
    }
}
