<div class="flex flex-col gap-8">
    <x-admin.page-header
        :kicker="__('Back office')"
        :heading="__('Portfolio overview')"
        :description="__('Monitor content, review inquiries, and jump into the private back office tools.')"
    >
        <flux:button :href="route('home')" target="_blank" rel="noreferrer" variant="ghost" icon:trailing="arrow-top-right-on-square">{{ __('View public site') }}</flux:button>
        <flux:button :href="route('admin.projects.create')" wire:navigate variant="primary" icon="plus">{{ __('Create project') }}</flux:button>
    </x-admin.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Projects'), 'value' => $projectCount, 'icon' => 'briefcase', 'href' => route('admin.projects.index')],
            ['label' => __('Published'), 'value' => $publishedProjectCount, 'icon' => 'globe-alt', 'href' => route('admin.projects.index')],
            ['label' => __('Featured Skills'), 'value' => $featuredSkillCount, 'icon' => 'cpu-chip', 'href' => route('admin.skills.index')],
            ['label' => __('New Inquiries'), 'value' => $newInquiryCount, 'icon' => 'inbox', 'href' => route('admin.inquiries.index')],
        ] as $stat)
            <a
                href="{{ $stat['href'] }}"
                wire:navigate
                class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 transition-colors hover:border-accent dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-accent"
            >
                <div class="flex items-center justify-between">
                    <p class="font-ui text-[11px] font-bold tracking-[0.2em] text-zinc-500 uppercase dark:text-zinc-400">{{ $stat['label'] }}</p>
                    <flux:icon :icon="$stat['icon']" variant="outline" class="size-5 text-zinc-400 transition-colors group-hover:text-accent-content" />
                </div>
                <p class="mt-4 font-display text-5xl leading-none font-black text-zinc-900 tabular-nums dark:text-white">{{ $stat['value'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <x-admin.panel :heading="__('Latest inquiries')" :description="__('New messages are listed first.')">
            <x-slot:actions>
                <flux:button :href="route('admin.inquiries.index')" wire:navigate size="sm" variant="ghost" icon:trailing="arrow-right">{{ __('All inquiries') }}</flux:button>
            </x-slot:actions>

            @if ($recentInquiries->isEmpty())
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No inquiries found.') }}</p>
            @else
                <ul class="-my-3 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($recentInquiries as $inquiry)
                        <li class="flex items-center justify-between gap-4 py-3" wire:key="dashboard-inquiry-{{ $inquiry->id }}">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $inquiry->name }}</p>
                                <p class="truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $inquiry->project_type ?: $inquiry->email }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <time datetime="{{ $inquiry->created_at?->toIso8601String() }}" class="hidden font-ui text-xs text-zinc-500 sm:block dark:text-zinc-400">{{ $inquiry->created_at?->diffForHumans() }}</time>
                                <x-admin.inquiry-status :status="$inquiry->status" />
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.panel>

        <x-admin.panel :heading="__('Quick actions')" :description="__('Jump straight into the most common edits.')">
            <div class="grid gap-2">
                @foreach ([
                    ['label' => __('Create project'), 'icon' => 'briefcase', 'href' => route('admin.projects.create')],
                    ['label' => __('Add skill'), 'icon' => 'cpu-chip', 'href' => route('admin.skills.create')],
                    ['label' => __('Edit portfolio copy'), 'icon' => 'pencil-square', 'href' => route('admin.settings.edit')],
                ] as $action)
                    <a
                        href="{{ $action['href'] }}"
                        wire:navigate
                        class="group flex items-center gap-3 rounded-lg border border-zinc-200 px-4 py-3 text-sm font-medium text-zinc-800 transition-colors hover:border-accent hover:text-accent-content dark:border-zinc-700 dark:text-zinc-100 dark:hover:border-accent dark:hover:text-accent-content"
                    >
                        <flux:icon :icon="$action['icon']" variant="outline" class="size-5 text-zinc-400 group-hover:text-accent-content" />
                        <span class="flex-1">{{ $action['label'] }}</span>
                        <flux:icon.arrow-right variant="micro" class="text-zinc-400 transition-transform group-hover:translate-x-0.5 group-hover:text-accent-content" />
                    </a>
                @endforeach
            </div>
        </x-admin.panel>
    </div>
</div>
