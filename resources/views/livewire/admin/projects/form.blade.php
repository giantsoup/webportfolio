<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $project ? 'Edit project' : 'Create project' }}</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Update the public project cards, detail pages, and metadata.</p>
        </div>

        <a href="{{ route('admin.projects.index') }}" wire:navigate class="text-sm font-medium text-zinc-600 dark:text-zinc-300">Back to projects</a>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:input wire:model="title" :label="__('Title')" required />
            <flux:input wire:model="slug" :label="__('Slug')" required />
            <flux:input wire:model="summary" :label="__('Summary')" required />

            <label class="grid gap-2">
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Body</span>
                <textarea wire:model="body" rows="12" class="rounded-xl border border-zinc-200 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
                @error('body') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </label>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="category" :label="__('Category')" required />
                <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" required />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="repo_url" :label="__('Repository URL')" type="url" />
                <flux:input wire:model="live_url" :label="__('Live URL')" type="url" />
            </div>

            <flux:input wire:model="case_study_url" :label="__('Case study URL')" type="url" />
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Publishing</h2>
                <div class="mt-5 space-y-4">
                    <flux:checkbox wire:model="is_featured" :label="__('Feature on the home page')" />
                    <flux:checkbox wire:model="is_published" :label="__('Publish project')" />
                    <flux:input wire:model="published_at" :label="__('Publish date')" type="datetime-local" />
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Featured image</h2>
                <div class="mt-5 space-y-4">
                    @if (filled($project?->featured_image_path))
                        <img src="{{ asset('storage/'.$project->featured_image_path) }}" alt="{{ $project->title }}" class="aspect-video w-full rounded-xl object-cover">
                    @endif

                    <input type="file" wire:model="featured_image" accept="image/*" class="block w-full text-sm text-zinc-600 dark:text-zinc-300">
                    @error('featured_image') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror

                    <div wire:loading wire:target="featured_image" class="text-sm text-zinc-500">Uploading image…</div>

                    @if (filled($project?->featured_image_path))
                        <button type="button" wire:click="removeFeaturedImage" class="text-sm font-medium text-red-600 dark:text-red-400">
                            Remove current image
                        </button>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Stack</h2>
                <div class="mt-5 grid gap-3">
                    @forelse ($allSkills as $skill)
                        <label class="flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300" wire:key="project-form-skill-{{ $skill->id }}">
                            <input type="checkbox" wire:model="skill_ids" value="{{ $skill->id }}" class="rounded border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800">
                            <span>{{ $skill->name }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-zinc-500">Add skills first to assign them here.</p>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-end">
                <flux:button type="submit">{{ __('Save project') }}</flux:button>
            </div>
        </div>
    </form>
</div>
