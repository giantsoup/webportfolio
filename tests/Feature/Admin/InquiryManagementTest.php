<?php

use App\Livewire\Admin\Inquiries\Index;
use App\Models\Inquiry;
use App\Models\User;
use Livewire\Livewire;

test('admin users can update inquiry status', function () {
    $this->actingAs(User::factory()->create());

    $inquiry = Inquiry::factory()->create([
        'status' => 'new',
        'contacted_at' => null,
    ]);

    Livewire::test(Index::class)
        ->call('markStatus', $inquiry, 'contacted')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('inquiries', [
        'id' => $inquiry->id,
        'status' => 'contacted',
    ]);

    expect($inquiry->fresh()->contacted_at)->not->toBeNull();
});
