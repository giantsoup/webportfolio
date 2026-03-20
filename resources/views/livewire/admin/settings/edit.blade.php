<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Portfolio settings</h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Manage the singleton content used across the public portfolio.</p>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="hero_kicker" :label="__('Hero kicker')" required />
                <flux:input wire:model="hero_title" :label="__('Hero title')" required />
                <flux:input wire:model="hero_emphasis" :label="__('Hero emphasis')" required />
            </div>

            <label class="grid gap-2">
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Hero summary</span>
                <textarea wire:model="hero_summary" rows="5" class="rounded-xl border border-zinc-200 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
            </label>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="availability_text" :label="__('Availability text')" required />
                <flux:input wire:model="location_label" :label="__('Location label')" />
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="years_experience" :label="__('Years experience')" type="number" min="0" required />
                <flux:input wire:model="projects_completed" :label="__('Projects completed')" type="number" min="0" required />
                <flux:input wire:model="schedule_label" :label="__('Schedule label')" />
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Social links</h2>
                <div class="mt-5 space-y-4">
                    <flux:input wire:model="github_url" :label="__('GitHub URL')" type="url" />
                    <flux:input wire:model="linkedin_url" :label="__('LinkedIn URL')" type="url" />
                    <flux:input wire:model="x_url" :label="__('X URL')" type="url" />
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Preview</h2>
                <div class="mt-5 rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">{{ $hero_kicker }}</p>
                    <p class="mt-3 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $hero_title }} <span class="text-fuchsia-500">{{ $hero_emphasis }}</span></p>
                    <p class="mt-4 text-sm leading-7 text-zinc-600 dark:text-zinc-300">{{ $hero_summary }}</p>
                </div>
            </div>

            <div class="flex justify-end">
                <flux:button type="submit">{{ __('Save settings') }}</flux:button>
            </div>
        </div>
    </form>
</div>
