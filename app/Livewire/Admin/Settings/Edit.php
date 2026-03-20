<?php

namespace App\Livewire\Admin\Settings;

use App\Http\Requests\UpdatePortfolioSettingRequest;
use App\Models\PortfolioSetting;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Portfolio Settings')]
class Edit extends Component
{
    public PortfolioSetting $settings;

    public string $hero_kicker = '';

    public string $hero_title = '';

    public string $hero_emphasis = '';

    public string $hero_summary = '';

    public string $availability_text = '';

    public int $years_experience = 0;

    public int $projects_completed = 0;

    public ?string $location_label = null;

    public ?string $schedule_label = null;

    public ?string $github_url = null;

    public ?string $linkedin_url = null;

    public ?string $x_url = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->settings = PortfolioSetting::query()->firstOrCreate(
            [],
            PortfolioSetting::defaults(),
        );

        $this->fill($this->settings->only([
            'hero_kicker',
            'hero_title',
            'hero_emphasis',
            'hero_summary',
            'availability_text',
            'years_experience',
            'projects_completed',
            'location_label',
            'schedule_label',
            'github_url',
            'linkedin_url',
            'x_url',
        ]));
    }

    /**
     * Persist the portfolio settings.
     */
    public function save(): void
    {
        $validated = $this->validate(UpdatePortfolioSettingRequest::rulesFor());

        $this->settings->fill($validated);
        $this->settings->save();

        session()->flash('status', 'Portfolio settings saved.');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.edit')->layout('layouts.app');
    }
}
