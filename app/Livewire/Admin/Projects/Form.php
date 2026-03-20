<?php

namespace App\Livewire\Admin\Projects;

use App\Http\Requests\UpsertProjectRequest;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Contracts\View\View;
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

    /**
     * Mount the component.
     */
    public function mount(?Project $project = null): void
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
        }
    }

    /**
     * Keep the slug aligned with the title until manually changed.
     */
    public function updatedTitle(string $value): void
    {
        if (! $this->project || $this->slug === '' || $this->slug === Str::slug($this->project->title)) {
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

        if (! $validated['is_published']) {
            $validated['published_at'] = null;
        } elseif (blank($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $project = $this->project ?? new Project;
        $project->fill($validated);
        $project->save();
        $project->skills()->sync($skillIds);

        $this->project = $project->fresh('skills');
        $this->featured_image = null;

        session()->flash('status', 'Project saved.');

        $this->redirectRoute('admin.projects.edit', ['project' => $project], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.projects.form', [
            'allSkills' => Skill::query()->orderBy('sort_order')->orderBy('name')->get(),
        ])->layout('layouts.app')
            ->title($this->project ? 'Edit Project' : 'Create Project');
    }
}
