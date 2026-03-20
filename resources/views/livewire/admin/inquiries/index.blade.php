<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Inquiries</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Review inbound messages from the public contact form.</p>
        </div>

        <div class="w-full md:w-56">
            <label class="grid gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                <span>Status</span>
                <select wire:model.live="status" class="rounded-xl border border-zinc-200 bg-white px-4 py-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                    <option value="">All</option>
                    <option value="new">New</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="contacted">Contacted</option>
                    <option value="archived">Archived</option>
                </select>
            </label>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($inquiries as $inquiry)
            <article class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900" wire:key="inquiry-row-{{ $inquiry->id }}">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $inquiry->name }}</h2>
                        <p class="mt-1 text-sm text-zinc-500">{{ $inquiry->email }} @if ($inquiry->company) · {{ $inquiry->company }} @endif</p>
                        @if ($inquiry->project_type)
                            <p class="mt-2 text-xs uppercase tracking-[0.18em] text-zinc-500">{{ $inquiry->project_type }}</p>
                        @endif
                    </div>

                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $inquiry->status }}
                    </span>
                </div>

                <p class="mt-5 whitespace-pre-line text-sm leading-7 text-zinc-700 dark:text-zinc-300">{{ $inquiry->message }}</p>

                <div class="mt-5 flex flex-wrap gap-3 text-sm">
                    <button type="button" wire:click="markStatus({{ $inquiry->id }}, 'reviewed')" class="font-medium text-zinc-700 dark:text-zinc-300">Mark reviewed</button>
                    <button type="button" wire:click="markStatus({{ $inquiry->id }}, 'contacted')" class="font-medium text-zinc-700 dark:text-zinc-300">Mark contacted</button>
                    <button type="button" wire:click="markStatus({{ $inquiry->id }}, 'archived')" class="font-medium text-zinc-700 dark:text-zinc-300">Archive</button>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-zinc-200 bg-white p-8 text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900">
                No inquiries found.
            </div>
        @endforelse
    </div>

    <div>
        {{ $inquiries->links() }}
    </div>
</div>
