<?php

namespace App\Livewire\Admin\Inquiries;

use App\Models\Inquiry;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Inquiries')]
class Index extends Component
{
    public string $status = '';

    /**
     * Update an inquiry status.
     */
    public function markStatus(Inquiry $inquiry, string $status): void
    {
        $inquiry->forceFill([
            'status' => $status,
            'contacted_at' => $status === 'contacted' ? now() : $inquiry->contacted_at,
        ])->save();

        session()->flash('status', 'Inquiry updated.');
    }

    public function render(): View
    {
        return view('livewire.admin.inquiries.index', [
            'inquiries' => Inquiry::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latestFirst()
                ->paginate(15),
        ])->layout('layouts.app');
    }
}
