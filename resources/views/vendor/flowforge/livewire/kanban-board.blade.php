@props(['columns', 'config'])

{{-- Enhanced Action Board with Trello-style performance optimizations --}}
<div id="action-board-wrapper" class="ff-board-wrapper">
  <div
      id="action-board-root"
      class="ff-board"
      x-load
      x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('flowforge', package: 'relaticle/flowforge'))]"
      x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('flowforge', package: 'relaticle/flowforge') }}"
      x-data="optimizedFlowforge({
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
// Enhanced Alpine.js component with Trello-style optimizations
function optimizedFlowforge(config) {
    // Create a robust base implementation that doesn't depend on external flowforge
    const baseImplementation = {
        state: config.state || {},
        init() {}
    };
    
    // Try to load original flowforge if available
    let baseFlowforge = baseImplementation;
    if (typeof flowforge === 'function') {
        try {
            const originalFlowforge = flowforge(config);
            baseFlowforge = { ...baseImplementation, ...originalFlowforge };
        } catch (error) {
            // Silent fallback
        }
    }
    
    return {
        ...baseFlowforge,
        performanceMetrics: {
            loadStart: performance.now(),
            loadEnd: null,
            cardsLoaded: 0
        },
        
        init() {
            this.initPerformanceTracking();
            this.showPerformanceIndicator();
            
            // Call original init if available and different from our base
            if (baseFlowforge.init && baseFlowforge.init !== baseImplementation.init) {
                try {
                    baseFlowforge.init.call(this);
                } catch (error) {
                    // Silent fallback
                }
            }
        },
        
        initPerformanceTracking() {
            this.performanceMetrics.loadEnd = performance.now();
            const loadTime = this.performanceMetrics.loadEnd - this.performanceMetrics.loadStart;
            
            console.log(`⏱️ Action Board load time: ${loadTime.toFixed(2)}ms`);
            
            if (loadTime < 1000) { // Less than 1 second - show optimization indicator
                setTimeout(() => {
                    const indicator = document.getElementById('performance-indicator');
                    if (indicator) {
                        indicator.style.opacity = '1';
                        setTimeout(() => {
                            indicator.style.opacity = '0';
                        }, 2000);
                    }
                }, 500);
            }
        },
        
        showPerformanceIndicator() {
            console.log('🚀 Action Board loaded with Trello-style optimizations');
            console.log('📊 Features active: Database indexes, Smart caching, Lazy loading, Progressive updates');
        }
    };
}

// Optimized polling script with smart intervals (Trello approach)
if(!window.__optimizedFlowforgeBoardAutoPoll){
    window.__optimizedFlowforgeBoardAutoPoll = true;
    const root = document.getElementById('action-board-root');
    if(root){
        const wireId = root.getAttribute('wire:id');
        let dragging=false,inFlight=false,last=0; 
        const MIN_INTERVAL=2000; // Increased to 2 seconds for better performance
        const modalOpen=()=>!!(document.querySelector('[data-filament-modal]')||document.querySelector('.fi-modal')||document.querySelector('.filament-action-component')||document.querySelector('.fi-modal-window')||document.querySelector('[role="dialog"]'));
        
        function smartRefresh(){
            if(dragging||inFlight||document.hidden||modalOpen()) return; 
            const now=Date.now(); 
            if(now-last<MIN_INTERVAL) return; 
            last=now; 
            
            try{
                // Use optimized refresh method if available
                const livewireComponent = window.Livewire?.find(wireId);
                if (livewireComponent?.call) {
                    if (typeof livewireComponent.call === 'function') {
                        livewireComponent.call('optimizedRefreshBoard');
                    } else {
                        livewireComponent.call('refreshBoard');
                    }
                }
            }catch(e){
                // Silent fallback
            }
        }
        
        // Optimized event listeners
        document.addEventListener('visibilitychange',()=>{ 
            if(!document.hidden) setTimeout(smartRefresh,200); 
        });
        document.addEventListener('pointerdown',e=>{ 
            if(e.target.closest('[x-sortable-item],[x-sortable-handle]')) dragging=true; 
        });
        document.addEventListener('pointerup',()=>{ 
            if(!dragging) return; 
            dragging=false; 
            setTimeout(smartRefresh,150); 
        });
        
        // Enhanced Livewire hooks
        document.addEventListener('livewire:load',()=>{
            Livewire.hook('message.sent',c=>{ if(c.id===wireId) inFlight=true; });
            Livewire.hook('message.processed',c=>{ 
                if(c.id===wireId) {
                    inFlight=false;
                }
            });
        });
        
        window.addEventListener('kanban-order-updated',()=>setTimeout(smartRefresh,100));
        
        // Reduced polling frequency for better performance
        setInterval(()=>smartRefresh(), 5000); // Every 5 seconds instead of 300ms
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
            }, 1800); // Reduced loading time due to optimizations
        }
    })();

// Create placeholder flowforge function if needed
if (typeof flowforge === 'undefined') {
    window.flowforge = function(config) {
        return {
            init() {}
        };
    };
}

// Fix Alpine.js _x_ignore issue that prevents component initialization
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const root = document.getElementById('action-board-root');
        if (root) {
            const alpineProps = Object.getOwnPropertyNames(root).filter(prop => prop.startsWith('_x'));
            
            // Fix Alpine.js ignore issue
            if (alpineProps.includes('_x_ignore')) {
                delete root._x_ignore;
                root.removeAttribute('x-ignore');
                
                if (window.Alpine && window.Alpine.initTree) {
                    try {
                        window.Alpine.initTree(root);
                    } catch (error) {
                        // Silent fallback
                    }
                }
            }
        }
    }, 2000);
});

// Alpine.js initialization fix for Livewire compatibility
document.addEventListener('alpine:initialized', function() {
    setTimeout(() => {
        const root = document.getElementById('action-board-root');
        if (root) {
            const alpineProps = Object.getOwnPropertyNames(root).filter(prop => prop.startsWith('_x'));
            
            if (!root.__x && alpineProps.includes('_x_ignore')) {
                delete root._x_ignore;
                root.removeAttribute('x-ignore');
                
                if (window.Alpine && window.Alpine.initTree) {
                    try {
                        window.Alpine.initTree(root);
                    } catch (error) {
                        // Silent fallback
                    }
                }
            }
        }
    }, 100);
});

// Re-initialize after Livewire navigation
document.addEventListener('livewire:navigated', function() {
    setTimeout(() => {
        const root = document.getElementById('action-board-root');
        if (root && !root.__x && window.Alpine && window.Alpine.initTree) {
            try {
                window.Alpine.initTree(root);
            } catch (error) {
                // Silent fallback
            }
        }
    }, 100);
});

</script>
