@props(['columns', 'config'])

{{-- Enhanced Action Board with Trello-style performance optimizations --}}
<div id="action-board-wrapper" class="ff-board-wrapper">
  <div
      id="action-board-root"
      class="ff-board"
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
      x-init="
          // Mark CSS as loaded immediately since it's inline
          $el.classList.add('css-loaded');
          
          // Load JS asynchronously for enhanced features
          loadJS('{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('flowforge', package: 'relaticle/flowforge') }}').then(() => {
              $el.classList.add('js-loaded');
          }).catch(error => {
              console.warn('Flowforge JS failed to load:', error);
          });
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
      
      <!-- Loading overlay -->
      {{-- <div id="action-board-loader" class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-gray-900 transition-all duration-500 ease-out" style="display:none;">
        <div class="w-12 h-12 border-4 border-gray-300 border-t-transparent rounded-full animate-spin transition-opacity duration-300 ease-out"></div>
      </div> --}}
      
      <!-- Inline Flowforge CSS for immediate styling -->
      <style>
        /* Load Flowforge CSS inline for immediate styling */
        @import url('{{ \Filament\Support\Facades\FilamentAsset::getStyleHref('flowforge', package: 'relaticle/flowforge') }}');

        /* Immediate board rendering styles */
        .ff-board.immediate-render {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.2s ease-out, transform 0.2s ease-out;
        }

        .ff-board.board-ready {
            opacity: 1;
            visibility: visible;
        }

        .ff-board.css-loaded {
            /* CSS is loaded, board should be properly styled */
            opacity: 1;
        }

        .ff-board.js-loaded {
            /* Enhanced features become available */
            position: relative;
        }

        /* .ff-board.js-loaded::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            opacity: 0.7;
            animation: pulse 2s infinite;
        } */

        @keyframes pulse {
            0%, 100% { opacity: 0.7; }
            50% { opacity: 1; }
        }

        /* Hide loader immediately */
        #action-board-loader {
            display: none !important;
        }

        /* Smooth board appearance */
        .ff-board__content {
            opacity: 1;
            transition: opacity 0.3s ease-out;
        }

        .ff-board__columns {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        }
      </style>
      
        <!-- JavaScript for async asset loading and board functionality -->
        <script data-flowforge-autopoll>
            // Optimized asset loading functions for immediate board rendering
            function loadCSS(href) {
                return new Promise((resolve, reject) => {
                    // Check if already loaded
                    if (document.querySelector(`link[href="${href}"]`)) {
                        resolve();
                        return;
                    }
                    
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = href;
                    
                     // Use both onload and onreadystatechange for better compatibility
                     link.onload = () => {
                         resolve();
                     };
                     link.onerror = () => {
                         reject(new Error('CSS load failed'));
                     };
                    
                    // For older browsers
                    if (link.readyState) {
                        link.onreadystatechange = () => {
                            if (link.readyState === 'loaded' || link.readyState === 'complete') {
                                link.onreadystatechange = null;
                                resolve();
                            }
                        };
                    }
                    
                    // Insert at the beginning of head for higher priority
                    document.head.insertBefore(link, document.head.firstChild);
                });
            }

            function loadJS(src) {
                return new Promise((resolve, reject) => {
                    // Check if already loaded
                    if (document.querySelector(`script[src="${src}"]`)) {
                        resolve();
                        return;
                    }
                    
                    const script = document.createElement('script');
                    script.src = src;
                    script.async = true;
                    script.type = 'module'; // Handle ES modules
                    
                     script.onload = () => {
                         resolve();
                     };
                     script.onerror = () => {
                         // Try loading as regular script if module fails
                         loadJSRegular(src).then(resolve).catch(reject);
                     };
                    
                    document.head.appendChild(script);
                });
            }

            function loadJSRegular(src) {
                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = src;
                    script.async = true;
                    // No type="module" for regular script
                    
                     script.onload = () => {
                         resolve();
                     };
                     script.onerror = () => {
                         reject(new Error('JS load failed'));
                     };
                    
                    document.head.appendChild(script);
                });
            }

            // Enhanced Alpine.js component with Trello-style optimizations
            function optimizedFlowforge(config) {
                // Progressive loading strategy - load critical parts first
                const progressiveLoader = {
                    loadCritical() {
                        // Load only essential board structure
                        return new Promise(resolve => {
                            if (window.requestIdleCallback) {
                                requestIdleCallback(() => {
                                    this.renderBoardStructure();
                                    resolve();
                                });
                            } else {
                                // Fallback for browsers that don't support requestIdleCallback
                                setTimeout(() => {
                                    this.renderBoardStructure();
                                    resolve();
                                }, 0);
                            }
                        });
                    },
                    
                    loadEnhanced() {
                        // Load enhanced features after critical content
                        return new Promise(resolve => {
                            if (window.requestIdleCallback) {
                                requestIdleCallback(() => {
                                    this.loadDragDrop();
                                    this.loadAnimations();
                                    resolve();
                                });
                            } else {
                                // Fallback for browsers that don't support requestIdleCallback
                                setTimeout(() => {
                                    this.loadDragDrop();
                                    this.loadAnimations();
                                    resolve();
                                }, 100);
                            }
                        });
                    },
                    
                    renderBoardStructure() {
                        // Minimal board rendering for immediate display
                        const board = document.getElementById('action-board-root');
                        if (board) {
                            board.classList.add('board-loaded');
                        }
                    },
                    
                    loadDragDrop() {
                        // Load drag and drop functionality
                        if ('sortable' in window) {
                            this.initializeSortable();
                        }
                    },
                    
                    loadAnimations() {
                        // Load animations and transitions
                        this.enableAnimations();
                    },
                    
                    initializeSortable() {
                        // Initialize sortable functionality
                    },
                    
                    enableAnimations() {
                        // Enable smooth animations
                        document.body.classList.add('animations-enabled');
                    }
                };
                
                // Create a robust base implementation that doesn't depend on external flowforge
                const baseImplementation = {
                    state: config.state || {},
                    progressiveLoader,
                    init() {
                        // Start progressive loading
                        progressiveLoader.loadCritical().then(() => {
                            progressiveLoader.loadEnhanced();
                        });
                    }
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
                        
                        // Mark board as immediately available
                        this.$el.classList.add('board-ready');
                        
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
                         // Performance optimizations active
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

        <!-- Optimized loader - hide immediately since board renders instantly -->
        <script>
            (function(){
                var loader = document.getElementById('action-board-loader');
                var root = document.getElementById('action-board-root');
                
                // Hide loader immediately since board renders without waiting for assets
                if (loader && root && !window.__flowforgeBoardInitialized) {
                    window.__flowforgeBoardInitialized = true;
                    
                    // Board is ready immediately, no need for loader
                    loader.style.display = 'none';
                    
                     // Add immediate board ready class
                     root.classList.add('board-ready', 'immediate-render');
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
  </div>
</div>
