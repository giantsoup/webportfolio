<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Portfolio overview</h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Monitor content, review inquiries, and jump into the private back office tools.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Projects</p>
            <p class="mt-3 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $projectCount }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Published</p>
            <p class="mt-3 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $publishedProjectCount }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Featured Skills</p>
            <p class="mt-3 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $featuredSkillCount }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">New Inquiries</p>
            <p class="mt-3 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $newInquiryCount }}</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Quick actions</h2>
            <div class="mt-5 flex flex-wrap gap-3">
                <flux:button :href="route('admin.projects.create')" wire:navigate>{{ __('Create project') }}</flux:button>
                <flux:button :href="route('admin.skills.create')" wire:navigate variant="filled">{{ __('Add skill') }}</flux:button>
                <flux:button :href="route('admin.settings.edit')" wire:navigate variant="ghost">{{ __('Edit portfolio copy') }}</flux:button>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Public site</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Open the guest-facing portfolio in a new tab to review the experience end-to-end.</p>
            <div class="mt-5">
                <a href="{{ route('home') }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">
                    View public site
                </a>
            </div>
        </div>
    </div>
</div>
