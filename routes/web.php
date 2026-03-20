<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Inquiries\Index as InquiryIndex;
use App\Livewire\Admin\Projects\Form as ProjectForm;
use App\Livewire\Admin\Projects\Index as ProjectIndex;
use App\Livewire\Admin\Settings\Edit as PortfolioSettingEdit;
use App\Livewire\Admin\Skills\Form as SkillForm;
use App\Livewire\Admin\Skills\Index as SkillIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/work', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/work/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:portfolio-contact')
    ->name('contact.store');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::prefix('dashboard')->name('admin.')->group(function (): void {
        Route::get('projects', ProjectIndex::class)->name('projects.index');
        Route::get('projects/create', ProjectForm::class)->name('projects.create');
        Route::get('projects/{project}/edit', ProjectForm::class)->name('projects.edit');

        Route::get('skills', SkillIndex::class)->name('skills.index');
        Route::get('skills/create', SkillForm::class)->name('skills.create');
        Route::get('skills/{skill}/edit', SkillForm::class)->name('skills.edit');

        Route::get('inquiries', InquiryIndex::class)->name('inquiries.index');
        Route::get('settings', PortfolioSettingEdit::class)->name('settings.edit');
    });
});

require __DIR__.'/settings.php';
