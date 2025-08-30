<x-filament-panels::page
    {{-- Page wrapper --}}
    @class([
        'fi-resource-edit-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
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
        <x-filament-panels::resources.relation-managers
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

    <x-filament-panels::page.unsaved-data-changes-alert />

    <!-- Copy Task URL Script -->
    <script>
        document.addEventListener('livewire:init', function () {
            // Listen for the copy-task-url event
            Livewire.on('copy-task-url', function (data) {
                const url = data.url;

                if (url) {
                    // Copy to clipboard
                    navigator.clipboard.writeText(url).then(function() {
                        // Show success notification using Filament's notification system
                        if (window.FilamentNotification) {
                            window.FilamentNotification.success('Task URL copied to clipboard!');
                        } else {
                            console.log('Task URL copied to clipboard:', url);
                        }
                    }).catch(function(err) {
                        console.error('Failed to copy task URL: ', err);
                        // Fallback for older browsers
                        const textArea = document.createElement('textarea');
                        textArea.value = url;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                        
                        // Show success notification for fallback
                        if (window.FilamentNotification) {
                            window.FilamentNotification.success('Task URL copied to clipboard!');
                        } else {
                            console.log('Task URL copied to clipboard (fallback):', url);
                        }
                    });
                }
            });
        });
    </script>
</x-filament-panels::page>
