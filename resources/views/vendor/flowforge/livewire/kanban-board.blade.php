@props(['columns', 'config'])

{{-- Published override to enable periodic polling refresh --}}
<div id="action-board-wrapper" class="ff-board-wrapper">
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
  <!-- Loading overlay -->
  <div id="action-board-loader" class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-gray-900 transition-all duration-500 ease-out" style="display:none;">
    <div class="w-12 h-12 border-4 border-gray-300 border-t-transparent rounded-full animate-spin transition-opacity duration-300 ease-out"></div>
  </div>
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

// Task sharing functionality (no notifications)
window.shareTaskUrl = function(event, taskId) {
  event.preventDefault();
  event.stopPropagation();

  // Use Filament's proper URL generation instead of hardcoded path
  // This matches the same URL structure used by the share task button in EditTask.php
  const editUrl = @js(\App\Filament\Resources\TaskResource::getUrl('edit', ['record' => 'PLACEHOLDER']));
  const fullUrl = editUrl.replace('PLACEHOLDER', taskId);

  navigator.clipboard.writeText(fullUrl).catch(function(err) {
      // No notification; ignore errors
  });
};
</script>

<!-- Show loader only on initial page load and hide after board loads -->
<script>
    (function(){
        var loader = document.getElementById('action-board-loader');
        var spinner = loader ? loader.querySelector('.animate-spin') : null;
        var root = document.getElementById('action-board-root');
        
        // Only show loader on initial page load (not Livewire updates)
        if (loader && spinner && root && !window.__flowforgeBoardInitialized) {
            window.__flowforgeBoardInitialized = true;
            
            // Show the loader immediately
            loader.style.display = 'flex';
            loader.style.opacity = '1';
            spinner.style.opacity = '1';
            
            // Hide the loader after the board content is loaded
            setTimeout(function(){
                spinner.style.opacity = '0';

                // Then fade out the entire background after spinner starts fading
                setTimeout(function(){
                    loader.style.opacity = '0';

                    // Finally hide the element completely
                    setTimeout(function(){
                        loader.style.display = 'none';
                    }, 800); // Wait for fade-out transition to complete
                }, 500); // Wait for spinner to start fading
            }, 4500); // Increased to 3000ms for longer loading screen
        }
    })();
</script>
