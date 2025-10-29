<x-filament-panels::page>
    @vite('resources/js/meeting-links.js')
    
    {{-- Viewers banner for Meeting Link edit --}}
    <x-viewers-banner channel="meeting-link-viewers" :id="$record->getKey()" />
    
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

    {{ $form() }}

    {{-- Unsaved Changes Alert --}}
    <x-filament-panels::page.unsaved-data-changes-alert />
    
</x-filament-panels::page>


