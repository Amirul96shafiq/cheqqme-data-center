@props([
    'footer' => null,
    'header' => null,
    'headerGroups' => null,
    'reorderable' => false,
    'reorderAnimationDuration' => 300,
])

<table
    {{ $attributes->class(['fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5']) }}
>
    @if ($header)
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
            @if ($headerGroups)
                <tr class="bg-gray-100/65 dark:bg-transparent backdrop-blur-lg">
                    {{ $headerGroups }}
                </tr>
            @endif

            <tr class="bg-gray-50/65 dark:bg-white/5 backdrop-blur-lg">
                {{ $header }}
            </tr>
        </thead>
    @endif

    <tbody
        @if ($reorderable)
            x-on:end.stop="$wire.reorderTable($event.target.sortable.toArray())"
            x-sortable
            data-sortable-animation-duration="{{ $reorderAnimationDuration }}"
        @endif
        class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5"
    >
        {{ $slot }}
    </tbody>

    @if ($footer)
        <tfoot class="bg-gray-50/65 dark:bg-white/5 backdrop-blur-lg">
            <tr>
                {{ $footer }}
            </tr>
        </tfoot>
    @endif
</table>
