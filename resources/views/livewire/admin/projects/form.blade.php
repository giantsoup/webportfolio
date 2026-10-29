<div class="flex flex-col gap-6">
    <x-admin.page-header
        :kicker="$project ? __('Projects / Edit') : __('Projects / New')"
        :heading="$project ? __('Edit project') : __('Create project')"
        :description="__('Update the public project cards, detail pages, and metadata.')"
    >
        <flux:button :href="route('admin.projects.index')" wire:navigate variant="ghost" icon="arrow-left">{{ __('Back to projects') }}</flux:button>
        @if ($project?->is_published && $project->published_at?->isPast())
            <flux:button :href="route('projects.show', $project)" target="_blank" rel="noreferrer" variant="ghost" icon:trailing="arrow-top-right-on-square">{{ __('View') }}</flux:button>
        @endif
        <flux:button type="submit" form="project-form" variant="primary" icon="check">{{ __('Save project') }}</flux:button>
    </x-admin.page-header>

    <x-admin.status />

    <form id="project-form" wire:submit="save" class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <x-admin.panel :heading="__('Content')" :description="__('What visitors read on the work index and the project page.')">
                <flux:input wire:model.live.blur="title" :label="__('Title')" required />
                <flux:input wire:model="slug" :label="__('Slug')" icon="link" class:input="font-mono" required />
                <flux:input wire:model="summary" :label="__('Summary')" required />
                <flux:textarea wire:model="body" :label="__('Body')" rows="14" />
            </x-admin.panel>

            <x-admin.panel :heading="__('Details')" :description="__('Classification and links shown alongside the project.')">
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:input wire:model="category" :label="__('Category')" required />
                    <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" required />
                    <flux:input wire:model="repo_url" :label="__('Repository URL')" type="url" icon="code-bracket" placeholder="https://" />
                    <flux:input wire:model="live_url" :label="__('Live URL')" type="url" icon="globe-alt" placeholder="https://" />
                </div>

                <flux:input wire:model="case_study_url" :label="__('Case study URL')" type="url" icon="document-text" placeholder="https://" />
            </x-admin.panel>
        </div>

        <div class="space-y-6">
            <x-admin.panel :heading="__('Publishing')">
                <flux:switch wire:model="is_published" :label="__('Publish project')" />
                <flux:switch wire:model="is_featured" :label="__('Feature on the home page')" />
                <flux:input wire:model="published_at" :label="__('Publish date')" type="datetime-local" />
            </x-admin.panel>

            <x-admin.panel :heading="__('GitHub')">
                @if ($githubConfigured && $project?->github_repository)
                    <x-slot:actions>
                        <flux:button wire:click="syncGitHub" size="sm" variant="ghost" icon="arrow-path" :tooltip="__('Refresh stats from GitHub')">{{ __('Sync') }}</flux:button>
                    </x-slot:actions>
                @endif

                @if ($githubConfigured)
                    <flux:select wire:model="github_repository" :label="__('Repository')">
                        <flux:select.option value="">{{ __('Not linked') }}</flux:select.option>
                        @foreach ($githubRepositoryOptions as $fullName)
                            <flux:select.option :value="$fullName" wire:key="github-option-{{ $fullName }}">{{ $fullName }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @else
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Set GITHUB_USERNAME in the environment to link this project to a repository.') }}</p>
                @endif

                @if ($project?->github_synced_at)
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        @foreach ([
                            __('Stars') => number_format($project->github_stars ?? 0),
                            __('Forks') => number_format($project->github_forks ?? 0),
                            __('Language') => $project->github_language ?? '—',
                            __('Last push') => $project->github_pushed_at?->diffForHumans() ?? '—',
                        ] as $label => $value)
                            <div wire:key="github-stat-{{ $loop->index }}">
                                <dt class="font-ui text-[11px] font-bold tracking-[0.2em] text-zinc-500 uppercase dark:text-zinc-400">{{ $label }}</dt>
                                <dd class="mt-0.5 font-medium text-zinc-900 tabular-nums dark:text-white">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if (filled($project->github_topics))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($project->github_topics as $topic)
                                <flux:badge size="sm" wire:key="github-topic-{{ $topic }}">{{ $topic }}</flux:badge>
                            @endforeach
                        </div>
                    @endif

                    @if (filled($project->github_readme))
                        <flux:modal.trigger name="github-readme">
                            <flux:button size="sm" icon="document-text" class="w-full">{{ __('View README') }}</flux:button>
                        </flux:modal.trigger>

                        <flux:modal name="github-readme" flyout class="w-full md:max-w-2xl">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('README') }}</flux:heading>
                                    <flux:text class="mt-1">{{ $project->github_repository }}</flux:text>
                                </div>

                                <div class="space-y-4 text-sm leading-6 break-words text-zinc-700 dark:text-zinc-300 [&_a]:text-accent-content [&_a]:underline [&_code]:font-mono [&_code]:text-xs [&_h1]:text-lg [&_h1]:font-semibold [&_h1]:text-zinc-900 dark:[&_h1]:text-white [&_h2]:text-base [&_h2]:font-semibold [&_h2]:text-zinc-900 dark:[&_h2]:text-white [&_h3]:font-semibold [&_img]:hidden [&_ol]:list-decimal [&_ol]:ps-5 [&_pre]:overflow-x-auto [&_pre]:rounded-md [&_pre]:bg-zinc-100 [&_pre]:p-3 dark:[&_pre]:bg-zinc-950 [&_ul]:list-disc [&_ul]:ps-5">
                                    {!! str($project->github_readme)->markdown(['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                                </div>

                                <div class="flex flex-col gap-3 border-t border-zinc-200 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700">
                                    <flux:text>{{ __('Replaces the body with the README as plain paragraphs. Save to keep it.') }}</flux:text>
                                    <flux:button wire:click="useReadmeAsBody" variant="primary" icon="arrow-down-on-square" class="shrink-0">{{ __('Use as body') }}</flux:button>
                                </div>
                            </div>
                        </flux:modal>
                    @endif

                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Synced :time', ['time' => $project->github_synced_at->diffForHumans()]) }} ·
                        <a href="https://github.com/{{ $project->github_repository }}" target="_blank" rel="noreferrer" class="underline underline-offset-2 hover:text-accent-content">{{ __('Open on GitHub') }}</a>
                    </p>
                @elseif ($githubConfigured)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Linking a repository records its stars, language, topics, last push and README when you save.') }}</p>
                @endif
            </x-admin.panel>

            <x-admin.panel :heading="__('Featured image')">
                @if (filled($project?->featured_image_path))
                    <img src="{{ asset('storage/'.$project->featured_image_path) }}" alt="{{ $project->title }}" class="aspect-video w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                @endif

                <flux:input type="file" wire:model="featured_image" accept="image/*" :label="filled($project?->featured_image_path) ? __('Replace image') : __('Upload image')" />

                <div wire:loading.flex wire:target="featured_image" class="items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <flux:icon.loading class="size-4" />
                    {{ __('Uploading image…') }}
                </div>

                @if (filled($project?->featured_image_path))
                    <flux:button wire:click="removeFeaturedImage" size="sm" variant="ghost" icon="trash" class="text-red-600! dark:text-red-400!">
                        {{ __('Remove current image') }}
                    </flux:button>
                @endif
            </x-admin.panel>

            <x-admin.panel :heading="__('Stack')">
                @if ($allSkills->isEmpty())
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Add skills first to assign them here.') }}</p>
                @else
                    <flux:checkbox.group wire:model="skill_ids">
                        @foreach ($allSkills as $skill)
                            <flux:checkbox :value="$skill->id" :label="$skill->name" wire:key="project-form-skill-{{ $skill->id }}" />
                        @endforeach
                    </flux:checkbox.group>
                @endif
            </x-admin.panel>

            <flux:button type="submit" variant="primary" icon="check" class="w-full">{{ __('Save project') }}</flux:button>
        </div>
    </form>
</div>
