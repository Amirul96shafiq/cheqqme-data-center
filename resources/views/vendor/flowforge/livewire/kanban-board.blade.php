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
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                }

                // Use Filament's proper URL generation instead of hardcoded path
                // This matches the same URL structure used by the share task button in EditTask.php
                const editUrl = @js(\App\Filament\Resources\TaskResource::getUrl('edit', ['record' => 'PLACEHOLDER']));
                const fullUrl = editUrl.replace('PLACEHOLDER', taskId);

                navigator.clipboard.writeText(fullUrl).catch(function(err) {
                    // No notification; ignore errors
                });
            };

            // Mobile-specific task sharing functionality
            window.shareTaskUrlMobile = function(event, taskId, buttonElement) {
                // Prevent default touch behavior and stop propagation
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                }

                // Use Filament's proper URL generation instead of hardcoded path
                const editUrl = @js(\App\Filament\Resources\TaskResource::getUrl('edit', ['record' => 'PLACEHOLDER']));
                const fullUrl = editUrl.replace('PLACEHOLDER', taskId);

                // Helper function to copy to clipboard with fallback
                const copyToClipboard = function(text) {
                    return new Promise(function(resolve, reject) {
                        // Try modern clipboard API first
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text)
                                .then(resolve)
                                .catch(function(err) {
                                    // Fallback to execCommand for older browsers/mobile
                                    const textarea = document.createElement('textarea');
                                    textarea.value = text;
                                    textarea.style.position = 'fixed';
                                    textarea.style.opacity = '0';
                                    textarea.style.left = '-999999px';
                                    textarea.style.top = '-999999px';
                                    document.body.appendChild(textarea);
                                    textarea.focus();
                                    textarea.select();
                                    
                                    try {
                                        const successful = document.execCommand('copy');
                                        document.body.removeChild(textarea);
                                        if (successful) {
                                            resolve();
                                        } else {
                                            reject(new Error('execCommand copy failed'));
                                        }
                                    } catch (err) {
                                        document.body.removeChild(textarea);
                                        reject(err);
                                    }
                                });
                        } else {
                            // Fallback to execCommand if clipboard API not available
                            const textarea = document.createElement('textarea');
                            textarea.value = text;
                            textarea.style.position = 'fixed';
                            textarea.style.opacity = '0';
                            textarea.style.left = '-999999px';
                            textarea.style.top = '-999999px';
                            document.body.appendChild(textarea);
                            textarea.focus();
                            textarea.select();
                            
                            try {
                                const successful = document.execCommand('copy');
                                document.body.removeChild(textarea);
                                if (successful) {
                                    resolve();
                                } else {
                                    reject(new Error('execCommand copy failed'));
                                }
                            } catch (err) {
                                document.body.removeChild(textarea);
                                reject(err);
                            }
                        }
                    });
                };

                // Try Web Share API first (better UX on mobile)
                if (navigator.share) {
                    try {
                        const shareData = {
                            title: 'Task',
                            url: fullUrl
                        };
                        
                        // Try sharing
                        navigator.share(shareData)
                            .then(function() {
                                // Show success bubble
                                if (buttonElement && typeof showCopiedBubble === 'function') {
                                    showCopiedBubble(buttonElement);
                                }
                            })
                            .catch(function(err) {
                                // User cancelled or share failed, fall back to clipboard
                                if (err.name !== 'AbortError') {
                                    copyToClipboard(fullUrl)
                                        .then(function() {
                                            if (buttonElement && typeof showCopiedBubble === 'function') {
                                                showCopiedBubble(buttonElement);
                                            }
                                        })
                                        .catch(function(err) {
                                            // Silently fail - could log here if needed
                                            console.error('Failed to copy to clipboard:', err);
                                        });
                                }
                            });
                    } catch (err) {
                        // Fall back to clipboard if Web Share API fails
                        copyToClipboard(fullUrl)
                            .then(function() {
                                if (buttonElement && typeof showCopiedBubble === 'function') {
                                    showCopiedBubble(buttonElement);
                                }
                            })
                            .catch(function(err) {
                                console.error('Failed to copy to clipboard:', err);
                            });
                    }
                } else {
                    // Fall back to clipboard API if Web Share API is not available
                    copyToClipboard(fullUrl)
                        .then(function() {
                            if (buttonElement && typeof showCopiedBubble === 'function') {
                                showCopiedBubble(buttonElement);
                            }
                        })
                        .catch(function(err) {
                            console.error('Failed to copy to clipboard:', err);
                        });
                }
            };

            // Hide empty columns when cards are dragged over them
            document.addEventListener('DOMContentLoaded', function() {
                // Add CSS for sortable drag behavior
                const style = document.createElement('style');
                style.textContent = `
                    .sortable-ghost, .fi-sortable-ghost {
                        background-color: rgba(20, 184, 166, 0.1) !important;
                        border: 1px dashed rgba(20, 184, 166, 0.6) !important;
                        border-radius: 8px !important;
                        position: relative !important;
                        transition: all 0.2s ease !important;
                        opacity: 0.6 !important;
                        box-shadow: 0 0 8px rgba(20, 184, 166, 0.2) !important;
                    }
                `;
                document.head.appendChild(style);
                
                // Teal preview cleanup system
                document.addEventListener('dragend', function(e) {
                    cleanupTealStyles();
                });
                
                function cleanupTealStyles() {
                    // Clean up elements marked with teal styles
                    const tealElements = document.querySelectorAll('[data-has-teal-styles="true"]');
                    tealElements.forEach(element => {
                        removeTealStyles(element);
                        element.dataset.hasTealStyles = 'false';
                    });
                    
                    // Clean up ghost elements
                    document.querySelectorAll('.sortable-ghost, .fi-sortable-ghost').forEach(removeTealStyles);
                }
                
                function removeTealStyles(element) {
                    ['background-color', 'border', 'box-shadow', 'border-radius'].forEach(prop => {
                        element.style.removeProperty(prop);
                    });
                }
                
                // Backup cleanup mechanism for mouse/touch events
                function forceCleanup() {
                    setTimeout(() => {
                        document.querySelectorAll('.ff-card').forEach(card => {
                            if (card.style.backgroundColor && card.style.backgroundColor.includes('rgba(20, 184, 166')) {
                                removeTealStyles(card);
                                card.dataset.hasTealStyles = 'false';
                            }
                        });
                    }, 100);
                }
                
                document.addEventListener('mouseup', forceCleanup);
                document.addEventListener('touchend', forceCleanup);
                
                function applyTealPreview(element) {
                    element.style.setProperty('background-color', 'rgba(20, 184, 166, 0.15)', 'important');
                    element.style.setProperty('border', '1px dashed rgba(20, 184, 166, 0.8)', 'important');
                    element.style.setProperty('box-shadow', '0 0 10px rgba(20, 184, 166, 0.3)', 'important');
                    element.style.setProperty('border-radius', '8px', 'important');
                    element.dataset.hasTealStyles = 'true';
                }
                
                // Use mutation observer to detect when sortable classes are added/removed
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            const target = mutation.target;
                            
                            // Handle drag interactions
                            if (target.classList.contains('sortable-ghost') || target.classList.contains('sortable-chosen')) {
                                const columnContent = target.closest('.ff-column__content');
                                if (columnContent) {
                                    const emptyColumn = columnContent.querySelector('.ff-empty-column');
                                    if (emptyColumn) {
                                        emptyColumn.style.display = 'none';
                                    }
                                }
                                
                                // Apply teal preview styling to ghost elements
                                if (target.classList.contains('sortable-ghost') || target.classList.contains('fi-sortable-ghost')) {
                                    applyTealPreview(target);
                                }
                                
                                // Cleanup when drag ends
                                if (!target.classList.contains('sortable-ghost') && 
                                    !target.classList.contains('fi-sortable-ghost') && 
                                    target.dataset.hasTealStyles === 'true') {
                                    removeTealStyles(target);
                                    target.dataset.hasTealStyles = 'false';
                                }
                            }
                        }
                        
                        // Check for child removals (when cards are moved out)
                        if (mutation.type === 'childList' && mutation.removedNodes.length > 0) {
                            const target = mutation.target;
                            if (target.classList.contains('ff-column__content')) {
                                // Check if column is now empty
                                const cards = target.querySelectorAll('.ff-card:not(.sortable-ghost):not(.sortable-chosen)');
                                const emptyColumn = target.querySelector('.ff-empty-column');
                                
                                const removeTimestamp = new Date().toISOString().substr(11, 12);
                                // console.log(`[${removeTimestamp}] Card removed - checking column: ${cards.length} cards, empty column exists: ${!!emptyColumn}, display: ${emptyColumn?.style.display || 'default'}`);
                                
                                if (cards.length === 0 && emptyColumn && emptyColumn.style.display === 'none') {
                                    const showTimestamp = new Date().toISOString().substr(11, 12);
                                    // console.log(`[${showTimestamp}] Showing empty column - card was removed`);
                                    emptyColumn.style.display = '';
                                }
                            }
                        }
                    });
                });
                
                // Observe all elements for class changes and child list changes
                observer.observe(document.body, {
                    attributes: true,
                    attributeFilter: ['class'],
                    childList: true,
                    subtree: true
                });
                
                // Listen for kanban events to show empty columns immediately
                window.addEventListener('kanban-order-updated', function() {
                    const timestamp = new Date().toISOString().substr(11, 12);
                    // console.log(`[${timestamp}] Kanban order updated - checking for empty columns`);
                    
                    // Use setTimeout to wait for Livewire to finish re-rendering
                    setTimeout(() => {
                        const checkTimestamp = new Date().toISOString().substr(11, 12);
                        // console.log(`[${checkTimestamp}] Checking columns after Livewire re-render`);
                        
                        // Check all columns for empty state
                        const allColumns = document.querySelectorAll('.ff-column__content');
                        allColumns.forEach((column, index) => {
                            const cards = column.querySelectorAll('.ff-card:not(.sortable-ghost):not(.sortable-chosen)');
                            const emptyColumn = column.querySelector('.ff-empty-column');
                            
                            // console.log(`[${checkTimestamp}] Column ${index}: ${cards.length} cards, empty column exists: ${!!emptyColumn}, display: ${emptyColumn?.style.display || 'default'}`);
                            
                            if (cards.length === 0 && emptyColumn && emptyColumn.style.display === 'none') {
                                const showTimestamp = new Date().toISOString().substr(11, 12);
                                // console.log(`[${showTimestamp}] Showing empty column after kanban update`);
                                emptyColumn.style.display = '';
                            }
                        });
                    }, 25); // Wait 50ms for Livewire to re-render
                });
                
            // console.log('Added CSS and mutation observer for drag behavior');
        });

            // Client-side filtering fallback for instant UX
            document.addEventListener('action-board-search', function(e){
                var term = (e?.detail?.search || '').toLowerCase();
                
                // Set global search state for Alpine.js
                window.searchActive = term.length > 0;
                
                const columns = document.querySelectorAll('.ff-column');
                let totalVisibleCards = 0;
                
                // console.log('Found columns:', columns.length);
                
                columns.forEach(function(col){
                    const cards = col.querySelectorAll('.ff-card');
                    let visible = 0;
                    cards.forEach(function(card){
                        const titleEl = card.querySelector('.ff-card__title');
                        const title = (titleEl?.textContent || '').toLowerCase();
                        const match = !term || title.includes(term);
                        card.style.display = match ? '' : 'none';
                        if (match) visible++;
                    });
                    
                    // Hide empty column elements when searching
                    const empty = col.querySelector('.ff-empty-column');
                    if (empty) {
                        if (term) {
                            // When searching, hide empty column elements
                            empty.style.display = 'none';
                        } else {
                            // When not searching, show empty column elements only if no cards are visible
                            empty.style.display = visible === 0 ? '' : 'none';
                        }
                    }
                    
                    const count = col.querySelector('.ff-column__count');
                    if (count) count.textContent = visible.toString();
                    
                    // Hide entire column when searching and no cards are visible
                    if (term) {
                        col.style.display = visible > 0 ? '' : 'none';
                    } else {
                        col.style.display = '';
                    }
                    
                    totalVisibleCards += visible;
                });
                
                // console.log('Total visible cards:', totalVisibleCards);
                
                // Show/hide no-results component
                const noResultsComponent = document.getElementById('no-results-component');
                const kanbanBoardContainer = document.getElementById('kanban-board-container');
                
                // console.log('DOM elements found:', { 
                //     noResultsComponent: !!noResultsComponent, 
                //     kanbanBoardContainer: !!kanbanBoardContainer 
                // });
                
                if (term && totalVisibleCards === 0) {
                    // Show no-results component when searching but no cards match
                    // console.log('Showing no-results component');
                    if (noResultsComponent) noResultsComponent.classList.remove('hidden');
                    if (kanbanBoardContainer) kanbanBoardContainer.classList.add('hidden');
                } else {
                    // Show kanban board when there are results or no search
                    // console.log('Showing kanban board');
                    if (noResultsComponent) noResultsComponent.classList.add('hidden');
                    if (kanbanBoardContainer) kanbanBoardContainer.classList.remove('hidden');
                }
                
                // Disable/enable card dragging based on search state
                const columnContents = document.querySelectorAll('.ff-column__content');
                const cards = document.querySelectorAll('.ff-card');
                
                columnContents.forEach(function(columnContent){
                    if (term) {
                        // When searching, disable dragging by removing sortable attributes
                        columnContent.removeAttribute('x-sortable');
                        columnContent.removeAttribute('x-sortable-group');
                        columnContent.removeAttribute('x-sortable-ghost-class');
                        columnContent.removeAttribute('x-sortable-chosen-class');
                        columnContent.removeAttribute('x-sortable-drag-class');
                        columnContent.removeAttribute('data-column-id');
                        columnContent.removeAttribute('@end.stop');
                    } else {
                        // When not searching, re-enable dragging by restoring sortable attributes
                        columnContent.setAttribute('x-sortable', '');
                        columnContent.setAttribute('x-sortable-group', 'cards');
                        columnContent.setAttribute('x-sortable-ghost-class', 'sortable-ghost');
                        columnContent.setAttribute('x-sortable-chosen-class', 'sortable-chosen');
                        columnContent.setAttribute('x-sortable-drag-class', 'sortable-drag');
                        columnContent.setAttribute('data-column-id', columnContent.closest('.ff-column').getAttribute('data-column-id') || '');
                        columnContent.setAttribute('@end.stop', '$wire.updateRecordsOrderAndColumn($event.to.getAttribute(\'data-column-id\'), $event.to.sortable.toArray())');
                    }
                });
                
                // Also disable/enable individual card sortable handles
                cards.forEach(function(card){
                    if (term) {
                        // When searching, disable card dragging
                        card.removeAttribute('x-sortable-handle');
                        card.removeAttribute('x-sortable-item');
                    } else {
                        // When not searching, re-enable card dragging
                        const cardId = card.getAttribute('data-card-id') || card.querySelector('[wire\\:key]')?.getAttribute('wire:key')?.replace('card-', '') || '';
                        if (cardId) {
                            card.setAttribute('x-sortable-handle', '');
                            card.setAttribute('x-sortable-item', cardId);
                        }
                    }
                });
            });
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
