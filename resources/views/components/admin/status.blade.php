@if (session('status'))
    <flux:callout {{ $attributes }} variant="success" icon="check-circle" :heading="session('status')" />
@endif
