<div class="flex flex-col gap-6">
    <x-admin.page-header
        :kicker="__('Projects / GitHub')"
        :heading="__('Import from GitHub')"
        :description="$githubUsername ? __('Public repositories owned by :username. Import opens a prefilled project to review; Import all creates unpublished drafts for everything listed.', ['username' => $githubUsername]) : __('Pick a repository to start a project from.')"
    >
        <flux:button :href="route('admin.projects.index')" wire:navigate variant="ghost" icon="arrow-left">{{ __('Back to projects') }}</flux:button>
        @if ($githubConfigured)
            <flux:button wire:click="refresh" icon="arrow-path">{{ __('Refresh') }}</flux:button>
            @if (! $failed)
                <flux:button
                    wire:click="importAll"
                    wire:confirm="{{ __('Import :count repositories? New projects are created as unpublished drafts; projects whose repository URL already matches are linked instead.', ['count' => $pendingCount]) }}"
                    variant="primary"
                    icon="arrow-down-tray"
                    :disabled="$pendingCount === 0"
                >{{ __('Import all (:count)', ['count' => $pendingCount]) }}</flux:button>
            @endif
        @endif
    </x-admin.page-header>

    <x-admin.status />

    @if (session('github_error'))
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="session('github_error')" />
    @endif

    @if (! $githubConfigured)
        <flux:callout variant="warning" icon="exclamation-triangle" :heading="__('GitHub is not connected yet')">
            <flux:callout.text>{{ __('Set GITHUB_USERNAME (and optionally GITHUB_TOKEN) in the environment to list your repositories here.') }}</flux:callout.text>
        </flux:callout>
    @elseif ($failed)
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="__('GitHub could not be reached')">
            <flux:callout.text>{{ __('Check the GitHub username and token, then refresh. The token may have expired.') }}</flux:callout.text>
        </flux:callout>
    @else
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="w-full sm:max-w-sm">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search repositories')" :aria-label="__('Search repositories')" clearable />
            </div>

            <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                <flux:switch wire:model.live="showForks" :label="__('Show forks')" />
                <flux:switch wire:model.live="showArchived" :label="__('Show archived')" />
            </div>
        </div>

        <x-admin.panel class="py-2">
            <flux:table class="relative">
                <flux:table.columns>
                    <flux:table.column>{{ __('Repository') }}</flux:table.column>
                    <flux:table.column class="max-md:hidden">{{ __('Language') }}</flux:table.column>
                    <flux:table.column class="max-sm:hidden" align="end">{{ __('Stars') }}</flux:table.column>
                    <flux:table.column class="max-lg:hidden">{{ __('Last push') }}</flux:table.column>
                    <flux:table.column align="end"><span class="sr-only">{{ __('Actions') }}</span></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($repositories as $repository)
                        @php
                            $repositoryKey = str($repository->fullName)->lower()->toString();
                            $linkedProject = $linkedProjects->get($repositoryKey);
                            $linkableProject = $linkableProjects->get($repositoryKey);
                        @endphp

                        <flux:table.row wire:key="github-repository-{{ $repository->fullName }}">
                            <flux:table.cell variant="strong" class="max-w-md whitespace-normal">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ $repository->htmlUrl }}" target="_blank" rel="noreferrer" class="hover:text-accent-content">{{ $repository->name }}</a>
                                    @if ($repository->isFork)
                                        <flux:badge size="sm">{{ __('Fork') }}</flux:badge>
                                    @endif
                                    @if ($repository->isArchived)
                                        <flux:badge size="sm" color="amber">{{ __('Archived') }}</flux:badge>
                                    @endif
                                </div>
                                @if ($repository->description)
                                    <p class="mt-1 line-clamp-2 text-sm font-normal text-zinc-500 dark:text-zinc-400">{{ $repository->description }}</p>
                                @endif
                                @if ($repository->topics)
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach ($repository->topics as $topic)
                                            <flux:badge size="sm" wire:key="github-repository-{{ $repository->fullName }}-{{ $topic }}">{{ $topic }}</flux:badge>
                                        @endforeach
                                    </div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="max-md:hidden">{{ $repository->language ?? '—' }}</flux:table.cell>
                            <flux:table.cell class="tabular-nums max-sm:hidden" align="end">{{ number_format($repository->stars) }}</flux:table.cell>
                            <flux:table.cell class="max-lg:hidden">
                                @if ($repository->pushedAt)
                                    <time datetime="{{ $repository->pushedAt->toIso8601String() }}" title="{{ $repository->pushedAt->toDayDateTimeString() }}">{{ $repository->pushedAt->diffForHumans() }}</time>
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                @if ($linkedProject)
                                    <flux:button :href="route('admin.projects.edit', $linkedProject)" wire:navigate size="sm" variant="ghost" icon="check-circle" :aria-label="__('Edit :name', ['name' => $linkedProject->title])">{{ __('Imported') }}</flux:button>
                                @elseif ($linkableProject)
                                    <flux:button wire:click="link({{ \Illuminate\Support\Js::from($repository->fullName) }})" size="sm" icon="link" :tooltip="__('Link to :title', ['title' => $linkableProject->title])">{{ __('Link') }}</flux:button>
                                @else
                                    <flux:button :href="route('admin.projects.create', ['repository' => $repository->fullName])" wire:navigate size="sm" variant="primary" icon="arrow-down-tray">{{ __('Import') }}</flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-10 text-center whitespace-normal">
                                {{ $totalCount === 0 ? __('No public repositories found.') : __('No repositories match these filters.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </x-admin.panel>
    @endif
</div>
