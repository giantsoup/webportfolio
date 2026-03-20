<?php

namespace App\Livewire\Admin\Skills;

use App\Models\Skill;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Skills')]
class Index extends Component
{
    /**
     * Delete a skill.
     */
    public function delete(Skill $skill): void
    {
        $skill->delete();

        session()->flash('status', 'Skill deleted.');
    }

    public function render(): View
    {
        return view('livewire.admin.skills.index', [
            'skills' => Skill::query()
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}
