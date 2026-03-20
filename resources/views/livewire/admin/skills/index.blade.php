<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Skills</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Manage the capability tiles and project taxonomy.</p>
        </div>

        <flux:button :href="route('admin.skills.create')" wire:navigate>{{ __('New skill') }}</flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr class="text-left text-xs uppercase tracking-[0.18em] text-zinc-500">
                    <th class="px-4 py-3">Skill</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Accent</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($skills as $skill)
                    <tr wire:key="skill-row-{{ $skill->id }}">
                        <td class="px-4 py-4">
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $skill->name }}</p>
                            <p class="mt-1 text-sm text-zinc-500">{{ $skill->slug }}</p>
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $skill->category }}</td>
                        <td class="px-4 py-4 text-sm"><span class="rounded-full px-2 py-1 text-xs font-medium" style="background-color: {{ $skill->accent_color }}20; color: {{ $skill->accent_color }};">{{ $skill->accent_color }}</span></td>
                        <td class="px-4 py-4">
                            <div class="flex justify-end gap-3 text-sm">
                                <a href="{{ route('admin.skills.edit', $skill) }}" wire:navigate class="font-medium text-zinc-900 dark:text-white">Edit</a>
                                <button type="button" wire:click="delete({{ $skill->id }})" wire:confirm="Delete this skill?" class="font-medium text-red-600 dark:text-red-400">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500">No skills yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
