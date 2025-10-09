@php
    $user = $getRecord();
    $coverImageUrl = $user->getFilamentCoverImageUrl();
@endphp

<x-user-avatar 
    :user="$user" 
    size="lg" 
    :cover-image-border="(bool) $coverImageUrl"
    :show-status="true"
    :lazy-load="false"
/>
