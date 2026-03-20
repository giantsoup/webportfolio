<x-mail::message>
# New Inquiry

You received a new portfolio inquiry from **{{ $inquiry->name }}**.

<x-mail::panel>
Email: {{ $inquiry->email }}

Company: {{ $inquiry->company ?: 'Not provided' }}

Project type: {{ $inquiry->project_type ?: 'Not provided' }}

Status: {{ ucfirst($inquiry->status) }}
</x-mail::panel>

{{ $inquiry->message }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
