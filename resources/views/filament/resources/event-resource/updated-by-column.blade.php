@php
    $record = $getRecord();
    $updatedAt = $record->updated_at;
    $createdAt = $record->created_at;
    $user = $record->updatedBy;
@endphp

<div class="px-4 py-3">
    @if (!$record->updated_by || ($updatedAt && $createdAt && $updatedAt->eq($createdAt)))
        <span class="text-sm text-gray-900 dark:text-white">-</span>
    @else
        <x-clickable-user-name
            :user="$user"
            :date="$updatedAt"
            fallbackText="Unknown"
        />
    @endif
</div>
