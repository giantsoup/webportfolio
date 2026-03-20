<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $skill ? 'Edit skill' : 'Create skill' }}</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Define the technical tiles used across the public portfolio.</p>
        </div>

        <a href="{{ route('admin.skills.index') }}" wire:navigate class="text-sm font-medium text-zinc-600 dark:text-zinc-300">Back to skills</a>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-[1fr_0.7fr]">
        <div class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:input wire:model="name" :label="__('Name')" required />
            <flux:input wire:model="slug" :label="__('Slug')" required />

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="category" :label="__('Category')" required />
                <flux:input wire:model="icon" :label="__('Icon label')" />
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input wire:model="accent_color" :label="__('Accent color')" required />
                    <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" required />
                </div>

                <div class="mt-5">
                    <flux:checkbox wire:model="is_featured" :label="__('Feature on the home page')" />
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">Preview</p>
                <div class="mt-4 rounded-2xl p-5" style="background-color: {{ $accent_color }}12;">
                    <p class="text-lg font-semibold" style="color: {{ $accent_color }};">{{ $name ?: 'Skill name' }}</p>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $category ?: 'Category' }}</p>
                </div>
            </div>

            <div class="flex justify-end">
                <flux:button type="submit">{{ __('Save skill') }}</flux:button>
            </div>
        </div>
    </form>
</div>
