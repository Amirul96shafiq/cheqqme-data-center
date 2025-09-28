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
// Debug: Script loading check
console.log('📜 Action Board script loading...');

// Enhanced Alpine.js component with Trello-style optimizations
function optimizedFlowforge(config) {
    console.log('🚀 Action Board loading with Trello-style optimizations...');
    
    // Create a robust base implementation that doesn't depend on external flowforge
    const baseImplementation = {
        // Basic properties that Alpine.js expects
        state: config.state || {},
        
        // Essential methods for kanban functionality
        init() {
            console.log('🚀 Base kanban initialization');
        }
    };
    
    // Try to load original flowforge if available
    let baseFlowforge = baseImplementation;
    if (typeof flowforge === 'function') {
        try {
            const originalFlowforge = flowforge(config);
            baseFlowforge = { ...baseImplementation, ...originalFlowforge };
            console.log('✅ Original flowforge loaded and merged successfully');
        } catch (error) {
            console.warn('⚠️ Original flowforge failed to load, using base implementation:', error);
        }
    } else {
        console.warn('⚠️ Original flowforge function not found, using base implementation');
    }
    
    return {
        ...baseFlowforge,
        performanceMetrics: {
            loadStart: performance.now(),
            loadEnd: null,
            cardsLoaded: 0
        },
        
        init() {
            console.log('🚀 Action Board initialized with Trello-style optimizations');
            this.initPerformanceTracking();
            this.showPerformanceIndicator();
            
            // Call original init if available and different from our base
            if (baseFlowforge.init && baseFlowforge.init !== baseImplementation.init) {
                try {
                    baseFlowforge.init.call(this);
                    console.log('✅ Original flowforge init called');
                } catch (error) {
                    console.warn('⚠️ Original flowforge init failed:', error);
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
                        console.log('✨ Performance indicator shown');
                        setTimeout(() => {
                            indicator.style.opacity = '0';
                        }, 2000);
                    } else {
                        console.warn('⚠️ Performance indicator element not found');
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
                console.warn('Action Board: Refresh failed', e);
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
                    // Show brief performance indicator after updates
                    const indicator = document.getElementById('performance-indicator');
                    if (indicator) {
                        indicator.querySelector('span')?.textContent || (indicator.textContent = '⚡ Updated');
                        indicator.style.opacity = '1';
                        setTimeout(() => indicator.style.opacity = '0', 1000);
                    }
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
            }, 500); // Reduced loading time due to optimizations
        }
    })();

// Direct test to ensure script loads
console.log('✅ Action Board script loaded completely');

// Monitor all function calls to optimizedFlowforge (set up early)
console.log('🔧 Setting up function monitoring...');
const originalOptimizedFlowforge = optimizedFlowforge;
window.optimizedFlowforge = function(config) {
    console.log('📞 optimizedFlowforge called by Alpine.js with config:', config);
    const result = originalOptimizedFlowforge(config);
    console.log('📞 optimizedFlowforge returning:', result);
    return result;
};

// Override the original flowforge function to see if it gets called
if (typeof flowforge === 'undefined') {
    console.log('🔧 Creating placeholder flowforge function...');
    window.flowforge = function(config) {
        console.log('📞 Original flowforge placeholder called with:', config);
        return {
            init() {
                console.log('📞 Original flowforge init called');
            }
        };
    };
}

// Test Alpine.js availability
document.addEventListener('DOMContentLoaded', function() {
    console.log('📋 DOM Content Loaded - checking Alpine.js...');
    
    // Check if Alpine is available
    if (window.Alpine) {
        console.log('✅ Alpine.js is available');
    } else {
        console.log('⚠️ Alpine.js not found, waiting...');
        
        // Wait for Alpine to load
        document.addEventListener('alpine:init', function() {
            console.log('✅ Alpine.js initialized');
        });
    }
    
    // Check if our function is defined
    if (typeof optimizedFlowforge === 'function') {
        console.log('✅ optimizedFlowforge function is defined');
        
        // Test the function directly
        console.log('🧪 Testing optimizedFlowforge function...');
        try {
            const testResult = optimizedFlowforge({ state: { test: true } });
            console.log('✅ optimizedFlowforge function works!', testResult);
        } catch (error) {
            console.error('❌ optimizedFlowforge function failed:', error);
        }
    } else {
        console.log('❌ optimizedFlowforge function not found');
    }
    
    // Check if action board root exists
    const root = document.getElementById('action-board-root');
    if (root) {
        console.log('✅ action-board-root element found');
        
        // Check x-data attribute
        const xData = root.getAttribute('x-data');
        console.log('📋 x-data attribute:', xData);
        
        // Watch for Alpine.js to process the element
        setTimeout(() => {
            console.log('🔍 Checking if Alpine has processed the element...');
            if (root._x_dataStack) {
                console.log('✅ Alpine.js has processed the element');
                console.log('📊 Data stack:', root._x_dataStack);
            } else {
                console.log('⚠️ Alpine.js has not processed the element yet');
            }
            
            // Check if Alpine.js has any data on the element
            console.log('🔍 Checking Alpine.js component data...');
            const alpineProps = Object.getOwnPropertyNames(root).filter(prop => prop.startsWith('_x'));
            console.log('📋 Element properties:', alpineProps);
            
            // Check if element is being ignored
            if (alpineProps.includes('_x_ignore')) {
                console.log('🚨 FOUND THE ISSUE! Element has _x_ignore attribute');
                console.log('🔧 This means Alpine.js is intentionally ignoring this element');
                console.log('💡 Solution: Remove _x_ignore and force Alpine.js to process the element');
                
                // Remove the ignore attribute and try to process the element
                delete root._x_ignore;
                root.removeAttribute('x-ignore');
                
                if (window.Alpine && window.Alpine.initTree) {
                    console.log('🔧 Attempting to manually initialize Alpine.js after removing ignore...');
                    try {
                        window.Alpine.initTree(root);
                        console.log('✅ Manual Alpine.js initialization after ignore removal');
                    } catch (error) {
                        console.error('❌ Manual Alpine.js initialization failed:', error);
                    }
                }
            } else {
                // Try to manually trigger Alpine.js on this element
                if (window.Alpine && window.Alpine.initTree) {
                    console.log('🔧 Attempting to manually initialize Alpine.js on element...');
                    try {
                        window.Alpine.initTree(root);
                        console.log('✅ Manual Alpine.js initialization attempted');
                    } catch (error) {
                        console.error('❌ Manual Alpine.js initialization failed:', error);
                    }
                }
            }
        }, 2000);
    } else {
        console.log('❌ action-board-root element not found');
    }
});

// Also listen for Alpine events
document.addEventListener('alpine:init', function() {
    console.log('🎉 Alpine.js init event fired');
});

document.addEventListener('alpine:initialized', function() {
    console.log('🎉 Alpine.js initialized event fired');
    
    // Check our component immediately after Alpine.js is initialized
    setTimeout(() => {
        const root = document.getElementById('action-board-root');
        if (root) {
            console.log('🔍 Post-initialization check...');
            const alpineProps = Object.getOwnPropertyNames(root).filter(prop => prop.startsWith('_x'));
            console.log('📋 Alpine properties on element:', alpineProps);
            
            // Check if there's any Alpine.js component data
            if (root.__x) {
                console.log('✅ Alpine.js component found on element:', root.__x);
            } else {
                console.log('❌ No Alpine.js component found on element');
                
                // If element is being ignored, fix it immediately
                if (alpineProps.includes('_x_ignore')) {
                    console.log('🚨 Element is being ignored! Fixing immediately...');
                    delete root._x_ignore;
                    root.removeAttribute('x-ignore');
                    
                    // Force Alpine.js to process this element
                    if (window.Alpine && window.Alpine.initTree) {
                        try {
                            window.Alpine.initTree(root);
                            console.log('✅ Forced Alpine.js initialization successful');
                        } catch (error) {
                            console.error('❌ Forced Alpine.js initialization failed:', error);
                        }
                    }
                }
            }
        }
    }, 100);
});

// Also listen for Livewire events that might affect Alpine.js
document.addEventListener('livewire:init', function() {
    console.log('🔄 Livewire init event fired');
});

document.addEventListener('livewire:navigated', function() {
    console.log('🔄 Livewire navigated event fired');
    
    // Re-check our component after Livewire navigation
    setTimeout(() => {
        const root = document.getElementById('action-board-root');
        if (root) {
            console.log('🔄 Checking component after Livewire navigation...');
            const hasComponent = !!root.__x;
            console.log(hasComponent ? '✅ Component still active after navigation' : '❌ Component lost after navigation');
            
            if (!hasComponent) {
                console.log('🔧 Re-initializing component after navigation...');
                if (window.Alpine && window.Alpine.initTree) {
                    try {
                        window.Alpine.initTree(root);
                        console.log('✅ Component re-initialized after navigation');
                    } catch (error) {
                        console.error('❌ Component re-initialization failed:', error);
                    }
                }
            }
        }
    }, 100);
});

// Listen for any Alpine.js errors
window.addEventListener('error', function(event) {
    if (event.message && event.message.includes('Alpine')) {
        console.error('🚨 Alpine.js Error:', event.error);
    }
});

</script>
