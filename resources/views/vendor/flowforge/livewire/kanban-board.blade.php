@props(['columns', 'config'])

{{-- Published override to enable periodic polling refresh --}}
<div
    class="ff-board"
    x-load
    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('flowforge', package: 'relaticle/flowforge'))]"
    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('flowforge', package: 'relaticle/flowforge') }}"
    x-data="flowforge({
        state: {
            columns: @js($columns),
            titleField: '{{ $config->getTitleField() }}',
            descriptionField: '{{ $config->getDescriptionField() }}',
            columnField: '{{ $config->getColumnField() }}',
            cardLabel: '{{ $config->getSingularCardLabel() }}',
            pluralCardLabel: '{{ $config->getPluralCardLabel() }}'
        }
    })"
    {{-- Custom JS polling (1s) with intelligent suppression to eliminate blink: no Livewire attribute polling. --}}
    x-init="
        const root = $el;
        const wireId = root.getAttribute('wire:id');
        let dragging = false;
        let inFlight = false;
        let lastRefreshAt = 0;
        const MIN_INTERVAL = 1000; // 1s
        function refresh(){
            if(dragging || inFlight || document.hidden) return;
            const now = Date.now();
            if(now - lastRefreshAt < MIN_INTERVAL) return;
            lastRefreshAt = now;
            try { window.Livewire?.find(wireId)?.call('refreshBoard'); } catch(e){}
        }
        document.addEventListener('visibilitychange', ()=>{ if(!document.hidden) setTimeout(refresh, 150); });
        // Detect drag start/end
        document.addEventListener('pointerdown', e=>{ if(e.target.closest('[x-sortable-item],[x-sortable-handle]')) dragging = true; });
        document.addEventListener('pointerup', ()=>{ if(!dragging) return; dragging=false; setTimeout(()=>refresh(), 120); });
        // Livewire request tracking
        document.addEventListener('livewire:load', ()=>{
            Livewire.hook('message.sent', (comp)=>{ if(comp.id===wireId) inFlight=true; });
            Livewire.hook('message.processed', (comp)=>{ if(comp.id===wireId) { inFlight=false; } });
        });
        // Resume immediately after server reorder event
        window.addEventListener('kanban-order-updated', ()=>{ setTimeout(()=>refresh(), 60); });
        // Interval loop
        setInterval(refresh, 350); // check frequently; refresh throttled by MIN_INTERVAL
    "
>
    <!-- Board Content -->
    <div class="ff-board__content">
        <div class="ff-board__columns kanban-board">
            @foreach($columns as $columnId => $column)
                <x-flowforge::column
                    :columnId="$columnId"
                    :column="$column"
                    :config="$config"
                    wire:key="column-{{ $columnId }}"
                />
            @endforeach
        </div>
    </div>

    <x-filament-actions::modals />
</div>
