@php
    $record = $getRecord();
    $email = $record->email;
@endphp

<div class="px-4 py-3">
    @if (empty($email))
        <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
    @else
        @php
            $displayEmail = \Illuminate\Support\Str::limit($email, 30, '...');
        @endphp
        <span class="text-sm text-gray-900 dark:text-white" title="{{ $email }}">
            {{ $displayEmail }}
        </span>
    @endif
</div>

