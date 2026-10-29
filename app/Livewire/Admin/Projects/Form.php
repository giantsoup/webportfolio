<?php

namespace App\Livewire\Admin\Projects;

use App\Http\Requests\UpsertProjectRequest;
use App\Models\Project;
use App\Models\Skill;
use App\Services\GitHub\GitHubClient;
use App\Services\GitHub\GitHubProjectImporter;
use App\Services\GitHub\GitHubRepository;
use App\Services\GitHub\GitHubRepositoryDetails;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?Project $project = null;

    public string $title = '';

    public string $slug = '';

    public string $summary = '';

    public string $body = '';

    public string $category = 'Custom Laravel';

    public ?string $repo_url = null;

    public ?string $live_url = null;

    public ?string $case_study_url = null;

    public bool $is_featured = false;

    public bool $is_published = false;

    public int $sort_order = 0;

    public ?string $published_at = null;

    public array $skill_ids = [];

    public mixed $featured_image = null;

    public ?string $github_repository = null;

    /**
     * Mount the component.
     */
    public function mount(GitHubClient $github, ?Project $project = null): void
    {
        $this->project = $project;

        if ($project) {
            $this->title = $project->title;
            $this->slug = $project->slug;
            $this->summary = $project->summary;
            $this->body = $project->body;
            $this->category = $project->category;
            $this->repo_url = $project->repo_url;
            $this->live_url = $project->live_url;
            $this->case_study_url = $project->case_study_url;
            $this->is_featured = $project->is_featured;
            $this->is_published = $project->is_published;
            $this->sort_order = $project->sort_order;
            $this->published_at = $project->published_at?->format('Y-m-d\TH:i');
            $this->skill_ids = $project->skills()->pluck('skills.id')->all();
            $this->github_repository = $project->github_repository;

            return;
        }

        if (is_string($fullName = request()->query('repository')) && filled($fullName)) {
            $this->prefillFromGitHub($github, $fullName);
        }
    }

    /**
     * Keep the slug aligned with the title until manually changed.
     */
    public function updatingTitle(string $value): void
    {
        if ($this->slug === '' || $this->slug === Str::slug($this->title)) {
            $this->slug = Str::slug($value);
        }
    }

    /**
     * Remove the stored featured image.
     */
    public function removeFeaturedImage(): void
    {
        if (! $this->project || blank($this->project->featured_image_path)) {
            return;
        }

        Storage::disk('public')->delete($this->project->featured_image_path);
        $this->project->forceFill(['featured_image_path' => null])->save();
        $this->project->refresh();

        session()->flash('status', 'Featured image removed.');
    }

    /**
     * Persist the project.
     */
    public function save(): void
    {
        $validated = $this->validate(UpsertProjectRequest::rulesFor($this->project));

        if ($this->featured_image) {
            if (filled($this->project?->featured_image_path)) {
                Storage::disk('public')->delete($this->project->featured_image_path);
            }

            $validated['featured_image_path'] = $this->featured_image->store('portfolio/projects', 'public');
        }

        unset($validated['featured_image']);

        $skillIds = $validated['skill_ids'] ?? [];
        unset($validated['skill_ids']);

        $validated['github_repository'] = blank($validated['github_repository'] ?? null) ? null : $validated['github_repository'];
        $linked = null;

        if ($validated['github_repository'] !== null && $validated['github_repository'] !== $this->project?->github_repository) {
            $linked = $this->fetchGitHubRepository(app(GitHubClient::class), $validated['github_repository']);

            if (! $linked) {
                return;
            }

            $validated['github_repository'] = $linked['repository']->fullName;
        }

        if (! $validated['is_published']) {
            $validated['published_at'] = null;
        } elseif (blank($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $project = $this->project ?? new Project;
        $project->fill($validated);
        $project->save();
        $project->skills()->sync($skillIds);

        if ($linked) {
            $project->syncFromGitHub($linked['repository'], $linked['details']);
        } elseif ($project->github_repository === null && $project->github_synced_at !== null) {
            $project->unlinkFromGitHub();
        }

        $this->project = $project->fresh('skills');
        $this->featured_image = null;

        session()->flash('status', 'Project saved.');

        $this->redirectRoute('admin.projects.edit', ['project' => $project], navigate: true);
    }

    /**
     * Refresh the linked repository's stats from GitHub.
     */
    public function syncGitHub(GitHubClient $github): void
    {
        if (! $this->project?->github_repository) {
            return;
        }

        $github->refresh();
        $linked = $this->fetchGitHubRepository($github, $this->project->github_repository);

        if (! $linked) {
            return;
        }

        $this->project->syncFromGitHub($linked['repository'], $linked['details']);
        $this->project->refresh();

        session()->flash('status', 'GitHub stats refreshed.');
    }

    /**
     * Replace the body with the plain text of the linked repository's stored README.
     */
    public function useReadmeAsBody(): void
    {
        $readme = GitHubProjectImporter::plainTextFromMarkdown($this->project?->github_readme);

        if ($readme === '') {
            return;
        }

        $this->body = $readme;
        $this->modal('github-readme')->close();
    }

    /**
     * Fill an unsaved project from one of the account's GitHub repositories.
     */
    private function prefillFromGitHub(GitHubClient $github, string $fullName): void
    {
        $linked = $this->fetchGitHubRepository($github, $fullName);

        if (! $linked) {
            return;
        }

        $importer = app(GitHubProjectImporter::class);

        foreach ($importer->attributesFor($linked['repository'], $linked['details']) as $attribute => $value) {
            $this->{$attribute} = $value;
        }

        $this->github_repository = $linked['repository']->fullName;
        $this->skill_ids = $importer->skillIdsFor($linked['repository'], $linked['details']);
    }

    /**
     * Look up one of the account's repositories with its languages and README, recording an
     * error when it is not found or GitHub cannot be reached.
     *
     * @return array{repository: GitHubRepository, details: GitHubRepositoryDetails}|null
     */
    private function fetchGitHubRepository(GitHubClient $github, string $fullName): ?array
    {
        try {
            $repository = $github->repository($fullName);

            if (! $repository) {
                $this->addError('github_repository', __('That repository could not be found on GitHub.'));

                return null;
            }

            return ['repository' => $repository, 'details' => $github->details($repository)];
        } catch (HttpClientException) {
            $this->addError('github_repository', __('GitHub could not be reached. Try again in a moment.'));

            return null;
        }
    }

    /**
     * Get the repositories offered in the GitHub link select, keeping the current link listed.
     *
     * @return Collection<int, string>
     */
    private function githubRepositoryOptions(GitHubClient $github): Collection
    {
        try {
            $options = $github->repositories()->map(fn (GitHubRepository $repository): string => $repository->fullName);
        } catch (HttpClientException) {
            $options = collect();
        }

        return $options
            ->when(filled($this->github_repository), fn (Collection $options) => $options->prepend($this->github_repository))
            ->unique(fn (string $fullName): string => Str::lower($fullName))
            ->values();
    }

    public function render(): View
    {
        $github = app(GitHubClient::class);

        return view('livewire.admin.projects.form', [
            'allSkills' => Skill::query()->orderBy('sort_order')->orderBy('name')->get(),
            'githubConfigured' => $github->isConfigured(),
            'githubRepositoryOptions' => $github->isConfigured() ? $this->githubRepositoryOptions($github) : collect(),
        ])->layout('layouts.app')
            ->title($this->project ? 'Edit Project' : 'Create Project');
    }
}
