@php
    $imagePath = is_callable($featuredImage) ? $featuredImage() : $featuredImage;
@endphp

@if($imagePath)
    <div class="w-full">
        <img
            src="{{ asset('storage/' . $imagePath) }}"
            alt="Featured Image"
            class="w-full max-h-64 object-cover rounded-lg"
            ondblclick="event.preventDefault(); event.stopPropagation(); return false;"
            ondragstart="event.preventDefault(); event.stopPropagation(); return false;"
            ondrag="event.preventDefault(); event.stopPropagation();"
            ondragend="event.preventDefault(); event.stopPropagation();"
            draggable="false"
            style="pointer-events: none; user-drag: none; -webkit-user-drag: none;"
        />
    </div>
@else
    <div class="w-full flex items-center justify-center p-8 border-2 border-dashed border-gray-300 rounded-lg text-gray-500">
        {{ $placeholder ?? __('event.no_featured_image') }}
    </div>
@endif
