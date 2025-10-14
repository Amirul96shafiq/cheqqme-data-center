@php
    $record = $getRecord();
    $user = $record->createdBy;
@endphp

@if ($user)
    <x-user-avatar 
        :user="$user" 
        size="sm" 
        :cover-image-border="false"
        :show-status="false"
        :lazy-load="false"
    />
@else
    <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
@endif
