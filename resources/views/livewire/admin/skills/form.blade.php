<div class="flex flex-col gap-6">
    <x-admin.page-header
        :kicker="$skill ? __('Skills / Edit') : __('Skills / New')"
        :heading="$skill ? __('Edit skill') : __('Create skill')"
        :description="__('Define the technical tiles used across the public portfolio.')"
    >
        <flux:button :href="route('admin.skills.index')" wire:navigate variant="ghost" icon="arrow-left">{{ __('Back to skills') }}</flux:button>
        <flux:button type="submit" form="skill-form" variant="primary" icon="check">{{ __('Save skill') }}</flux:button>
    </x-admin.page-header>

    <x-admin.status />

    <form id="skill-form" wire:submit="save" class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <x-admin.panel :heading="__('Skill')">
            <flux:input wire:model.live.debounce.300ms="name" :label="__('Name')" required />
            <flux:input wire:model="slug" :label="__('Slug')" icon="link" class:input="font-mono" required />

            <div class="grid gap-6 md:grid-cols-2">
                <flux:input wire:model.live.debounce.300ms="category" :label="__('Category')" required />
                <flux:input wire:model="icon" :label="__('Icon label')" />
            </div>
        </x-admin.panel>

        <div class="space-y-6">
            <x-admin.panel :heading="__('Display')">
                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:input wire:model.live.debounce.300ms="accent_color" :label="__('Accent color')" class:input="font-mono" required />
                    <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" required />
                </div>

                <flux:switch wire:model="is_featured" :label="__('Feature on the home page')" />
            </x-admin.panel>

            <x-admin.panel :heading="__('Preview')">
                @php($previewColor = preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $accent_color) ? $accent_color : '#a3a2af')
                <div class="rounded-lg border p-5" style="border-color: {{ $previewColor }}40; background-color: {{ $previewColor }}12;">
                    <p class="text-lg font-semibold" style="color: {{ $previewColor }};">{{ $name ?: 'Skill name' }}</p>
                    <p class="mt-1 font-ui text-[11px] font-bold tracking-[0.2em] text-zinc-500 uppercase dark:text-zinc-400">{{ $category ?: 'Category' }}</p>
                </div>
            </x-admin.panel>

            <flux:button type="submit" variant="primary" icon="check" class="w-full">{{ __('Save skill') }}</flux:button>
        </div>
    </form>
</div>
