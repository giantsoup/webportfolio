<div class="flex flex-col gap-6">
    <x-admin.page-header
        :kicker="__('Content')"
        :heading="__('Portfolio settings')"
        :description="__('Manage the singleton content used across the public portfolio.')"
    >
        <flux:button :href="route('home')" target="_blank" rel="noreferrer" variant="ghost" icon:trailing="arrow-top-right-on-square">{{ __('View public site') }}</flux:button>
        <flux:button type="submit" form="portfolio-settings-form" variant="primary" icon="check">{{ __('Save settings') }}</flux:button>
    </x-admin.page-header>

    <x-admin.status />

    <form id="portfolio-settings-form" wire:submit="save" class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="space-y-6">
            <x-admin.panel :heading="__('Cover')" :description="__('The headline block at the top of the home page.')">
                <div class="grid gap-6 md:grid-cols-3">
                    <flux:input wire:model.live.debounce.300ms="hero_kicker" :label="__('Hero kicker')" required />
                    <flux:input wire:model.live.debounce.300ms="hero_title" :label="__('Hero title')" required />
                    <flux:input wire:model.live.debounce.300ms="hero_emphasis" :label="__('Hero emphasis')" required />
                </div>

                <flux:textarea wire:model.live.debounce.500ms="hero_summary" :label="__('Hero summary')" rows="5" />
            </x-admin.panel>

            <x-admin.panel :heading="__('Profile facts')" :description="__('Short facts shown around the site and in the footer.')">
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:input wire:model="availability_text" :label="__('Current focus text')" required />
                    <flux:input wire:model="location_label" :label="__('Location label')" icon="map-pin" />
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <flux:input wire:model="years_experience" :label="__('Years experience')" type="number" min="0" required />
                    <flux:input wire:model="projects_completed" :label="__('Projects completed')" type="number" min="0" required />
                    <flux:input wire:model="schedule_label" :label="__('Schedule label')" icon="clock" />
                </div>
            </x-admin.panel>
        </div>

        <div class="space-y-6">
            <x-admin.panel :heading="__('Preview')" :description="__('Approximates the home page cover.')">
                <div class="overflow-hidden rounded-lg border border-portfolio-night-line bg-portfolio-night p-5 text-portfolio-bone">
                    <p class="font-ui text-[10px] font-bold tracking-[0.24em] break-words text-portfolio-signal uppercase">{{ $hero_kicker }}</p>
                    <p class="mt-3 font-display text-4xl leading-[0.85] font-black break-words uppercase">
                        <span class="block">{{ $hero_title }}</span>
                        <span class="mt-1 inline-block -rotate-1 bg-portfolio-signal px-1.5 text-portfolio-night">{{ $hero_emphasis }}</span>
                    </p>
                    <p class="mt-4 line-clamp-4 text-sm leading-6 text-portfolio-fog">{{ $hero_summary }}</p>
                </div>
            </x-admin.panel>

            <x-admin.panel :heading="__('Social links')">
                <flux:input wire:model="github_url" :label="__('GitHub URL')" type="url" icon="code-bracket" placeholder="https://" />
                <flux:input wire:model="linkedin_url" :label="__('LinkedIn URL')" type="url" icon="link" placeholder="https://" />
                <flux:input wire:model="x_url" :label="__('X URL')" type="url" icon="at-symbol" placeholder="https://" />
            </x-admin.panel>

            <flux:button type="submit" variant="primary" icon="check" class="w-full">{{ __('Save settings') }}</flux:button>
        </div>
    </form>
</div>
