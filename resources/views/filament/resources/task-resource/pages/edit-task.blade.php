<x-filament-panels::page
    {{-- Page wrapper --}}
    @class([
        'fi-resource-edit-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    {{-- JavaScript for scroll to comments functionality --}}
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('scroll-to-comments', function () {
                // Find the comments section
                const commentsSection = document.querySelector('.comments-pane');
                if (commentsSection) {
                    // Scroll to the comments section with smooth behavior
                    commentsSection.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
    {{-- Form content --}}
    @capture($form)
        <x-filament-panels::form
            id="form"
            :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
            wire:submit="save"
        >
            {{ $this->form }}

            {{-- Form actions --}}
            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    @endcapture

    @php
        // Get relation managers and check if there are combined relation manager tabs with content
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    {{-- Display form --}}
    @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
        {{ $form() }}
    @endif

    {{-- Display relation managers --}}
    @if (count($relationManagers))
        <x-filament-panels::resources.relation-managers>
            :active-locale="isset($activeLocale) ? $activeLocale : null"
            :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
            :content-tab-label="$this->getContentTabLabel()"
            :content-tab-icon="$this->getContentTabIcon()"
            :content-tab-position="$this->getContentTabPosition()"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        >
            @if ($hasCombinedRelationManagerTabsWithContent)
                <x-slot name="content">
                    {{ $form() }}
                </x-slot>
            @endif
        </x-filament-panels::resources.relation-managers>
    @endif
</x-filament-panels::page>
