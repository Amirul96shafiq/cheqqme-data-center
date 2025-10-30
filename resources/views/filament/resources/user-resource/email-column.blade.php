@php
    $record = $getRecord();
    $email = $record->email;
@endphp

@if (empty($email))
    <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
@else
    @php
        $displayEmail = \Illuminate\Support\Str::limit($email, 30, '...');
    @endphp
    <span title="{{ $email }}">
        {{ $displayEmail }}
    </span>
@endif

