@php
    $user = $getRecord();
    $coverImageUrl = $user->getFilamentCoverImageUrl();
@endphp

<div class="px-4 py-3">
    <x-user-avatar
        :user="$user"
        size="lg"
        :cover-image-border="(bool) $coverImageUrl"
        :show-status="true"
        :lazy-load="true"
    />
</div>
