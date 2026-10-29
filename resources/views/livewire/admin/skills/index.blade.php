<div class="flex flex-col gap-6">
    <x-admin.page-header
        :kicker="__('Content')"
        :heading="__('Skills')"
        :description="__('Manage the capability tiles and project taxonomy.')"
    >
        <flux:button :href="route('admin.skills.create')" wire:navigate variant="primary" icon="plus">{{ __('New skill') }}</flux:button>
    </x-admin.page-header>

    <x-admin.status />

    <x-admin.panel class="py-2">
        <flux:table class="relative">
            <flux:table.columns>
                <flux:table.column>{{ __('Skill') }}</flux:table.column>
                <flux:table.column class="max-sm:hidden">{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Accent') }}</flux:table.column>
                <flux:table.column align="end"><span class="sr-only">{{ __('Actions') }}</span></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($skills as $skill)
                    <flux:table.row wire:key="skill-row-{{ $skill->id }}">
                        <flux:table.cell variant="strong">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.skills.edit', $skill) }}" wire:navigate class="hover:text-accent-content">{{ $skill->name }}</a>
                                @if ($skill->is_featured)
                                    <flux:badge size="sm" color="rose" icon="star">{{ __('Featured') }}</flux:badge>
                                @endif
                            </div>
                            <p class="mt-0.5 font-mono text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ $skill->slug }}</p>
                        </flux:table.cell>
                        <flux:table.cell class="max-sm:hidden">{{ $skill->category }}</flux:table.cell>
                        <flux:table.cell>
                            <span class="inline-flex items-center gap-2 font-mono text-xs">
                                <span class="size-3 rounded-full ring-1 ring-zinc-900/10 dark:ring-white/20" style="background-color: {{ $skill->accent_color }};"></span>
                                {{ $skill->accent_color }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-1">
                                <flux:button :href="route('admin.skills.edit', $skill)" wire:navigate size="sm" variant="ghost" icon="pencil-square" :tooltip="__('Edit')" :aria-label="__('Edit :name', ['name' => $skill->name])" />
                                <flux:button wire:click="delete({{ $skill->id }})" wire:confirm="Delete this skill?" size="sm" variant="ghost" icon="trash" class="text-red-600! dark:text-red-400!" :tooltip="__('Delete')" :aria-label="__('Delete :name', ['name' => $skill->name])" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="py-10 text-center">{{ __('No skills yet.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </x-admin.panel>
</div>
