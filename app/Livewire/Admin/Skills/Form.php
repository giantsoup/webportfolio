<?php

namespace App\Livewire\Admin\Skills;

use App\Http\Requests\UpsertSkillRequest;
use App\Models\Skill;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class Form extends Component
{
    public ?Skill $skill = null;

    public string $name = '';

    public string $slug = '';

    public string $category = 'Backend';

    public ?string $icon = null;

    public string $accent_color = '#df8eff';

    public int $sort_order = 0;

    public bool $is_featured = false;

    /**
     * Mount the component.
     */
    public function mount(?Skill $skill = null): void
    {
        $this->skill = $skill;

        if ($skill) {
            $this->name = $skill->name;
            $this->slug = $skill->slug;
            $this->category = $skill->category;
            $this->icon = $skill->icon;
            $this->accent_color = $skill->accent_color;
            $this->sort_order = $skill->sort_order;
            $this->is_featured = $skill->is_featured;
        }
    }

    /**
     * Keep the slug aligned with the name until manually changed.
     */
    public function updatedName(string $value): void
    {
        if (! $this->skill || $this->slug === '' || $this->slug === Str::slug($this->skill->name)) {
            $this->slug = Str::slug($value);
        }
    }

    /**
     * Persist the skill.
     */
    public function save(): void
    {
        $validated = $this->validate(UpsertSkillRequest::rulesFor($this->skill));

        $skill = $this->skill ?? new Skill;
        $skill->fill($validated);
        $skill->save();

        session()->flash('status', 'Skill saved.');

        $this->redirectRoute('admin.skills.edit', ['skill' => $skill], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.skills.form')
            ->layout('layouts.app')
            ->title($this->skill ? 'Edit Skill' : 'Create Skill');
    }
}
