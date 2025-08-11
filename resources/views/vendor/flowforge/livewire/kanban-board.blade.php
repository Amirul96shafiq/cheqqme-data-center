@props(['columns', 'config'])

{{-- Published override to enable periodic polling refresh --}}
<div
    id="action-board-root"
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

<script data-flowforge-autopoll>
// Lightweight polling script isolated from markup to avoid visible code issues.
if(!window.__flowforgeBoardAutoPoll){
    window.__flowforgeBoardAutoPoll = true;
    const root = document.getElementById('action-board-root');
    if(root){
        const wireId = root.getAttribute('wire:id');
        let dragging=false,inFlight=false,last=0; const MIN_INTERVAL=1000;
        const modalOpen=()=>!!(document.querySelector('[data-filament-modal]')||document.querySelector('.fi-modal')||document.querySelector('.filament-action-component')||document.querySelector('.fi-modal-window')||document.querySelector('[role="dialog"]'));
        function refresh(){
            if(dragging||inFlight||document.hidden||modalOpen()) return; const now=Date.now(); if(now-last<MIN_INTERVAL) return; last=now; try{window.Livewire?.find(wireId)?.call('refreshBoard');}catch(e){}
        }
        document.addEventListener('visibilitychange',()=>{ if(!document.hidden) setTimeout(refresh,120); });
        document.addEventListener('pointerdown',e=>{ if(e.target.closest('[x-sortable-item],[x-sortable-handle]')) dragging=true; });
        document.addEventListener('pointerup',()=>{ if(!dragging) return; dragging=false; setTimeout(refresh,110); });
        document.addEventListener('livewire:load',()=>{
            Livewire.hook('message.sent',c=>{ if(c.id===wireId) inFlight=true; });
            Livewire.hook('message.processed',c=>{ if(c.id===wireId) inFlight=false; });
        });
        window.addEventListener('kanban-order-updated',()=>setTimeout(refresh,50));
        setInterval(()=>refresh(),300);
    }
}
</script>
