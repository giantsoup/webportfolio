<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Projects</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Manage the public work index and project detail pages.</p>
        </div>

        <flux:button :href="route('admin.projects.create')" wire:navigate>{{ __('New project') }}</flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr class="text-left text-xs uppercase tracking-[0.18em] text-zinc-500">
                    <th class="px-4 py-3">Project</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Published</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($projects as $project)
                    <tr wire:key="project-row-{{ $project->id }}">
                        <td class="px-4 py-4">
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $project->title }}</p>
                            <p class="mt-1 text-sm text-zinc-500">{{ $project->slug }}</p>
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $project->category }}</td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $project->is_published ? 'Published' : 'Draft' }}
                            @if ($project->is_featured)
                                <span class="ml-2 rounded-full bg-zinc-100 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">Featured</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $project->published_at?->format('M j, Y') ?? '—' }}</td>
                        <td class="px-4 py-4">
                            <div class="flex justify-end gap-3 text-sm">
                                <a href="{{ route('admin.projects.edit', $project) }}" wire:navigate class="font-medium text-zinc-900 dark:text-white">Edit</a>
                                <button type="button" wire:click="delete({{ $project->id }})" wire:confirm="Delete this project?" class="font-medium text-red-600 dark:text-red-400">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500">No projects yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $projects->links() }}
    </div>
</div>
