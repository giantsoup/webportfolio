<?php

namespace App\Livewire\Admin\Inquiries;

use App\Models\Inquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Inquiries')]
class Index extends Component
{
    use WithPagination;

    public string $status = '';

    /**
     * Return to the first page when the status filter changes.
     */
    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Update an inquiry status.
     */
    public function markStatus(Inquiry $inquiry, string $status): void
    {
        validator(['status' => $status], [
            'status' => ['required', Rule::in(Inquiry::STATUSES)],
        ])->validate();

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
