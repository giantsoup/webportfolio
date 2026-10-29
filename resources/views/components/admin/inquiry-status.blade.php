@props(['status'])

<flux:badge {{ $attributes }} size="sm" :color="match ($status) {
    'new' => 'rose',
    'reviewed' => 'amber',
    'contacted' => 'emerald',
    default => 'zinc',
}">{{ str($status)->headline() }}</flux:badge>
