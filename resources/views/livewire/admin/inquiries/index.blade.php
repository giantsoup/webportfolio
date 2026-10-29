<div class="flex flex-col gap-6">
    <x-admin.page-header
        :kicker="__('Inbox')"
        :heading="__('Inquiries')"
        :description="__('Review inbound messages from the public contact form.')"
    >
        <flux:select wire:model.live="status" :label="__('Status')" class="w-full sm:w-48">
            <flux:select.option value="">{{ __('All') }}</flux:select.option>
            @foreach (\App\Models\Inquiry::STATUSES as $statusOption)
                <flux:select.option :value="$statusOption">{{ __(str($statusOption)->headline()->toString()) }}</flux:select.option>
            @endforeach
        </flux:select>
    </x-admin.page-header>

    <x-admin.status />

    <div class="space-y-4">
        @forelse ($inquiries as $inquiry)
            <article
                wire:key="inquiry-row-{{ $inquiry->id }}"
                @class([
                    'relative overflow-hidden rounded-xl border bg-white p-6 dark:bg-zinc-900',
                    'border-accent/50 dark:border-accent/50' => $inquiry->status === 'new',
                    'border-zinc-200 dark:border-zinc-700' => $inquiry->status !== 'new',
                ])
            >
                @if ($inquiry->status === 'new')
                    <span class="absolute inset-y-0 start-0 w-1 bg-accent" aria-hidden="true"></span>
                @endif

                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">{{ $inquiry->name }}</h2>
                        <p class="mt-1 text-sm break-words text-zinc-500 dark:text-zinc-400">
                            <a href="mailto:{{ $inquiry->email }}" class="text-zinc-700 underline decoration-zinc-300 underline-offset-2 hover:text-accent-content hover:decoration-current dark:text-zinc-300 dark:decoration-zinc-600">{{ $inquiry->email }}</a>
                            @if ($inquiry->company) · {{ $inquiry->company }} @endif
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <time datetime="{{ $inquiry->created_at?->toIso8601String() }}" title="{{ $inquiry->created_at?->toDayDateTimeString() }}" class="font-ui text-xs text-zinc-500 dark:text-zinc-400">{{ $inquiry->created_at?->diffForHumans() }}</time>
                        <x-admin.inquiry-status :status="$inquiry->status" />
                    </div>
                </div>

                @if ($inquiry->project_type)
                    <p class="mt-4 font-ui text-[11px] font-bold tracking-[0.2em] text-zinc-500 uppercase dark:text-zinc-400">{{ $inquiry->project_type }}</p>
                @endif

                <p class="mt-3 text-sm leading-7 whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $inquiry->message }}</p>

                <div class="mt-5 flex flex-wrap gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:button wire:click="markStatus({{ $inquiry->id }}, 'reviewed')" size="sm" icon="eye" :disabled="$inquiry->status === 'reviewed'">{{ __('Mark reviewed') }}</flux:button>
                    <flux:button wire:click="markStatus({{ $inquiry->id }}, 'contacted')" size="sm" icon="chat-bubble-left-right" :disabled="$inquiry->status === 'contacted'">{{ __('Mark contacted') }}</flux:button>
                    <flux:button wire:click="markStatus({{ $inquiry->id }}, 'archived')" size="sm" variant="ghost" icon="archive-box" :disabled="$inquiry->status === 'archived'">{{ __('Archive') }}</flux:button>
                </div>
            </article>
        @empty
            <x-admin.panel class="py-12 text-center">
                <flux:icon.inbox variant="outline" class="mx-auto size-8 text-zinc-400" />
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No inquiries found.') }}</p>
            </x-admin.panel>
        @endforelse
    </div>

    @if ($inquiries->hasPages())
        <flux:pagination :paginator="$inquiries" />
    @endif
</div>
