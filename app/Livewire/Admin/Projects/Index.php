<?php

namespace App\Livewire\Admin\Projects;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Projects')]
class Index extends Component
{
    /**
     * Delete a project.
     */
    public function delete(Project $project): void
    {
        if (filled($project->featured_image_path)) {
            Storage::disk('public')->delete($project->featured_image_path);
        }

        $project->delete();

        session()->flash('status', 'Project deleted.');
    }

    public function render(): View
    {
        return view('livewire.admin.projects.index', [
            'projects' => Project::query()
                ->with('skills')
                ->ordered()
                ->paginate(12),
        ])->layout('layouts.app');
    }
}
