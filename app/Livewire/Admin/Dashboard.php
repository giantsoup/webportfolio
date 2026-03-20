<?php

namespace App\Livewire\Admin;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.admin.dashboard', [
            'projectCount' => Project::query()->count(),
            'publishedProjectCount' => Project::query()->published()->count(),
            'featuredSkillCount' => Skill::query()->where('is_featured', true)->count(),
            'newInquiryCount' => Inquiry::query()->where('status', 'new')->count(),
        ])->layout('layouts.app');
    }
}
