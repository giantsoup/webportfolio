<div class="flex flex-col gap-6">
    <x-admin.page-header
        :kicker="__('Content')"
        :heading="__('Projects')"
        :description="__('Manage the public work index and project detail pages.')"
    >
        <flux:button :href="route('admin.projects.import')" wire:navigate icon="arrow-down-tray">{{ __('Import from GitHub') }}</flux:button>
        <flux:button :href="route('admin.projects.create')" wire:navigate variant="primary" icon="plus">{{ __('New project') }}</flux:button>
    </x-admin.page-header>

    <x-admin.status />

    <x-admin.panel class="py-2">
        <flux:table :paginate="$projects" class="relative">
            <flux:table.columns>
                <flux:table.column>{{ __('Project') }}</flux:table.column>
                <flux:table.column class="max-md:hidden">{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column class="max-sm:hidden">{{ __('Published') }}</flux:table.column>
                <flux:table.column align="end"><span class="sr-only">{{ __('Actions') }}</span></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($projects as $project)
                    <flux:table.row wire:key="project-row-{{ $project->id }}">
                        <flux:table.cell variant="strong" class="max-w-xs">
                            <a href="{{ route('admin.projects.edit', $project) }}" wire:navigate class="block truncate hover:text-accent-content">{{ $project->title }}</a>
                            <p class="mt-0.5 truncate font-mono text-xs font-normal text-zinc-500 dark:text-zinc-400">/work/{{ $project->slug }}</p>
                        </flux:table.cell>
                        <flux:table.cell class="max-md:hidden">{{ $project->category }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1.5">
                                <flux:badge size="sm" :color="$project->is_published ? 'emerald' : 'zinc'">{{ $project->is_published ? __('Published') : __('Draft') }}</flux:badge>
                                @if ($project->is_featured)
                                    <flux:badge size="sm" color="rose" icon="star">{{ __('Featured') }}</flux:badge>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="tabular-nums max-sm:hidden">{{ $project->published_at?->format('M j, Y') ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-1">
                                <flux:button :href="route('admin.projects.edit', $project)" wire:navigate size="sm" variant="ghost" icon="pencil-square" :tooltip="__('Edit')" :aria-label="__('Edit :name', ['name' => $project->title])" />
                                <flux:button wire:click="delete({{ $project->id }})" wire:confirm="Delete this project?" size="sm" variant="ghost" icon="trash" class="text-red-600! dark:text-red-400!" :tooltip="__('Delete')" :aria-label="__('Delete :name', ['name' => $project->title])" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-10 text-center">{{ __('No projects yet.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </x-admin.panel>
</div>
