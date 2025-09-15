<!-- Task Comments Component -->
<div class="flex flex-col flex-1 h-full min-h-0 rounded-xl" 
     x-data="notificationHandler()"
     x-on:keydown.ctrl.enter.prevent="
         ($event.target.closest('[data-composer]') || $event.target.closest('.minimal-comment-editor')) && 
         ($wire.editingId === null || $wire.editingId === undefined) && 
         $wire.addComment().then(() => {
             setTimeout(() => {
                 const editor = document.querySelector('[data-composer] trix-editor');
                 if (editor) {
                     editor.textContent = '';
                     editor.innerHTML = '';
                     editor.dispatchEvent(new Event('input', { bubbles: true }));
                 }
             }, 100);
         })
     ">
    <!-- Composer (Top) -->
    <div class="px-0 pt-0 pb-5" data-composer>
        <div class="space-y-3">
            <div class="fi-form" wire:ignore.self>
                {{ $this->composerForm }} <!-- Filament RichEditor -->
            </div>
            @error('newComment') 
                <p class="text-xs text-danger-600" 
                   wire:key="error-newComment-{{ time() }}"
                   x-data="{ show: true }" 
                   x-show="show" 
                   x-init="
                       setTimeout(() => show = false, 3000);
                       $wire.on('comment-added', () => show = false);
                   "
                   x-transition:leave="transition ease-in duration-300"
                   x-transition:leave-start="opacity-100"
                   x-transition:leave-end="opacity-0">
                   {{ $message }}
                </p> 
            @enderror <!-- Error message -->
            <!-- Button to add a new comment -->
            <button wire:click="addComment" wire:loading.attr="disabled" wire:target="addComment" type="button" class="w-full inline-flex items-center justify-center gap-1.5 p-2.5 bg-primary-600 hover:bg-primary-500 text-primary-900 text-sm font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500/50 disabled:opacity-50 relative">
                <div class="flex items-center gap-1.5">
                    <span wire:loading.remove wire:target="addComment">{{ __('comments.composer.send') }}</span>
                    <span wire:loading wire:target="addComment">{{ __('comments.composer.saving') }}</span>
                </div>
                <div class="absolute right-2 flex items-center gap-1 text-primary-800 text-[11px] font-semibold">
                    <kbd class="px-1 py-0.5 bg-primary-transparent border border-primary-800 rounded font-mono">CTRL + ENTER</kbd>
                </div>
            </button>
        </div>
    </div>
    <!-- Comments List (scroll area) -->
    <div class="flex-1 min-h-0 px-0 pb-0">
        <div class="px-4 py-4 text-sm overflow-y-auto custom-thin-scroll h-full comment-list-container" data-comment-list>
            <div class="space-y-6">
                <!-- Loop through comments -->
                @forelse($this->comments as $comment)
                    <div class="group relative flex gap-3" wire:key="comment-{{ $comment->id }}" data-comment-id="{{ $comment->id }}">
                        <div class="flex-shrink-0 relative">
                        @php
                            $avatarPath = $comment->user->avatar ?? null;
                            $avatarUrl = $avatarPath ? \Storage::url($avatarPath) : null;
                        @endphp
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $comment->user->username ?? __('comments.meta.user_fallback') }}" class="w-10 h-10 rounded-full object-cover ring-1 ring-white/20 dark:ring-gray-800 shadow-sm relative z-10 {{ auth()->id() === $comment->user_id ? 'border-2 border-primary-500/80' : '' }}" loading="lazy">
                        @else
                            <!-- Default avatar if no avatar is set -->
                            @php
                                $defaultAvatarUrl = (new \Filament\AvatarProviders\UiAvatarsProvider())->get($comment->user);
                            @endphp
                            @if($defaultAvatarUrl)
                                <img src="{{ $defaultAvatarUrl }}" alt="{{ $comment->user->username ?? __('comments.meta.user_fallback') }}" class="w-10 h-10 rounded-full object-cover ring-1 ring-white/20 dark:ring-gray-800 shadow-sm relative z-10 {{ auth()->id() === $comment->user_id ? 'border-2 border-primary-500/80' : '' }}" loading="lazy">
                            @else
                                <div class="w-10 h-10 rounded-full bg-primary-500 ring-1 ring-white/20 dark:ring-gray-800 shadow-sm flex items-center justify-center relative z-10 {{ auth()->id() === $comment->user_id ? 'border-2 border-white/80' : '' }}">
                                    <span class="text-sm font-medium text-white">
                                        {{ substr($comment->user->username ?? __('comments.meta.user_fallback'), 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        @endif
                        <!-- Vertical connecting line that extends from avatar -->
                        <div class="absolute left-1/2 top-10 w-[0.5px] {{ auth()->id() === $comment->user_id ? 'bg-primary-500/80' : 'bg-gray-300/80 dark:bg-gray-600/80' }} transform -translate-x-1/2 z-0" style="height: calc(100% + 1.5rem);"></div>
                        </div>
                        <!-- Comment content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-col">
                                        <span class="comment-username text-gray-900 dark:text-gray-100 leading-none">{{ $comment->user->username ?? __('comments.meta.unknown') }}</span>
                                        <span class="mt-1 comment-meta text-gray-500 dark:text-gray-400" title="{{ $comment->created_at->format('j/n/y, h:i A') }}">
                                            {{ $comment->created_at->diffForHumans(short: true) }} · {{ $comment->created_at->format('j/n/y, h:i A') }}
                                        @if($comment->updated_at->gt($comment->created_at))
                                                <span class="italic text-gray-400 comment-meta">· {{ __('comments.meta.edited') }}</span>
                                        @endif
                                    </span>
                                </div>
                                <!-- Edit and Delete buttons -->
                                @if(auth()->id() === $comment->user_id)
                                    <div class="flex items-center gap-1">
                                        @if($this->editingId !== $comment->id)
                                            <!-- Edit button -->
                                            <button type="button" wire:click="startEdit({{ $comment->id }})" class="p-1.5 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 focus:outline-none focus:ring-2 focus:ring-primary-500/40 transition-all duration-200" title="{{ __('comments.buttons.edit') }}">
                                                @svg('heroicon-o-pencil-square', 'w-4 h-4 transition-transform duration-200')
                                            </button>
                                            <!-- Delete button -->
                                            <button type="button" wire:click="confirmDelete({{ $comment->id }})" class="p-1.5 rounded-md text-gray-400 hover:text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 focus:outline-none focus:ring-2 focus:ring-danger-500/40 transition-all duration-200" title="{{ __('comments.buttons.delete') }}">
                                                @svg('heroicon-o-trash', 'w-4 h-4 transition-transform duration-200')
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2">
                                <!-- Edit form -->
                                @if($this->editingId === $comment->id)
                                    <div class="space-y-2">
                                        <div class="fi-form edit-form" wire:ignore.self data-edit-form="true">{{ $this->editForm }}</div>
                                        <div class="flex items-center gap-2">
                                            <!-- Save Edit form button -->
                                            <button wire:click="saveEdit" 
                                                    type="button" 
                                                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-primary-600 text-primary-900 hover:bg-primary-500 hover:dark:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                                                    wire:loading.attr="disabled"
                                                    wire:target="saveEdit">
                                                <span wire:loading.remove wire:target="saveEdit">
                                                    {{ __('comments.buttons.save') }}
                                                </span>
                                                <span wire:loading wire:target="saveEdit">
                                                    {{ __('comments.buttons.submitting') }}
                                                </span>
                                            </button>
                                            <!-- Cancel Edit form button -->
                                            <button wire:click="cancelEdit" type="button" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500/50">{{ __('comments.buttons.cancel') }}</button>
                                        </div>
                                    </div>
                                @else
                                    <!-- Comment content -->
                                    <div class="bg-gray-300/15 dark:bg-gray-800/50 rounded-lg p-3 mt-4">
                                        <div class="prose prose-xs dark:prose-invert max-w-none leading-snug text-[13px] text-gray-700 dark:text-gray-300 break-words">{!! $comment->rendered_comment !!}</div>
                                    </div>
                                    
                                    <!-- Comment Reactions -->
                                    <x-comment-reactions :comment="$comment" />
                                @endif
                            </div>
                    </div>
                </div>
            <!-- No comments -->
            @empty
                <div class="px-2 py-8 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">{{ __('comments.list.none') }}</p>
                </div>
            @endforelse
            </div>
            <!-- Show total comments -->
            @if($this->totalComments > 0)
                <div class="mt-3 text-[10px] text-gray-400 text-center relative z-10">{{ __('comments.list.showing', ['shown' => $this->comments->count(), 'total' => $this->totalComments]) }}</div>
            @endif
            <!-- Show more comments button -->
            @if($this->totalComments > $visibleCount)
                @php $remaining = $this->totalComments - $visibleCount; @endphp
                <div class="mt-2 relative z-10">
                    <button wire:click="showMore" 
                            type="button" 
                            class="w-full text-xs font-medium px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500/40 relative z-10"
                            wire:loading.attr="disabled"
                            wire:target="showMore">
                        <span wire:loading.remove wire:target="showMore">
                            {{ __('comments.list.show_more', ['count' => ($remaining < 5 ? $remaining : 5)]) }}
                        </span>
                        <span wire:loading wire:target="showMore">
                            {{ __('comments.list.loading') }}
                        </span>
                    </button>
                </div>
            @endif
        </div>
    </div>
    <!-- Delete comment modal -->
    @if($confirmingDeleteId)
        <!-- Elevated z-index to ensure overlay sits above form action buttons -->
        <div wire:ignore
            x-data
            x-init="
                const root = document.documentElement;
                root.classList.add('comment-delete-open');
                const prev = document.activeElement;
                $nextTick(() => { $el.querySelector('[data-modal-initial]')?.focus(); });
                // Fallback safety: ensure class removed if this element is ever removed without Alpine cleanup
                const observer = new MutationObserver(() => {
                    if (!document.body.contains($el)) {
                        root.classList.remove('comment-delete-open');
                        observer.disconnect();
                        prev && prev.focus && prev.focus();
                    }
                });
                observer.observe(document.body, { childList: true, subtree: true });
            "
            x-on:keydown.window.escape.prevent.stop="$wire.cancelDelete()"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 pointer-events-auto comment-delete-modal-container"
        >
            <!-- Delete comment modal backdrop -->
            <div class="comment-delete-modal-backdrop absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75" wire:click="cancelDelete" aria-hidden="true"></div>
            <!-- Delete comment modal -->
            <div role="dialog" aria-modal="true" aria-labelledby="delete-comment-heading" class="comment-delete-modal fi-modal-window relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">
                <!-- Delete comment modal close button -->
                <button type="button" wire:click="cancelDelete" class="fi-modal-close-btn absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900" aria-label="{{ __('comments.modal.delete.close') }}">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <!-- Delete comment modal content -->
                <div class="flex flex-col items-center text-center">
                    <div class="mb-5 flex items-center justify-center">
                        <div class="p-3 rounded-full bg-danger-100 text-danger-600 dark:bg-danger-500/20 dark:text-danger-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </div>
                    </div>
                    <!-- Delete comment modal heading -->
                    <h2 id="delete-comment-heading" class="fi-modal-heading text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('comments.modal.delete.title') }}</h2>
                    <!-- Delete comment modal description -->
                    <p class="fi-modal-description mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ __('comments.modal.delete.description') }}</p>
                    <!-- Delete comment modal actions -->
                    <div class="mt-6 flex w-full items-stretch gap-3">
                        <!-- Delete comment modal cancel button -->
                        <button data-modal-initial type="button" wire:click="cancelDelete" class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">{{ __('comments.modal.delete.cancel') }}</button>
                        <!-- Delete comment modal confirm button -->
                        <button type="button" wire:click="performDelete" class="fi-btn fi-color-danger flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-danger-600 text-white hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-danger-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-danger-600 dark:hover:bg-danger-500 dark:focus:ring-offset-gray-900">{{ __('comments.modal.delete.confirm') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Single backdrop already blocks clicks; extra blocker removed -->
    @endif
    <!-- Ensure the helper class is cleared when modal not present -->
    @if(!$confirmingDeleteId)
        <script>
            // Ensure the helper class is cleared when modal not present
            document.documentElement.classList.remove('comment-delete-open');
        </script>
    @endif
    
    <!-- Custom styles -->
    <style>
        /* Set the placeholder text as a CSS custom property */
        .minimal-comment-editor {
            --comment-placeholder: @json(__('comments.composer.placeholder'));
        }
        
        /* Responsive height for comments list */
        .comment-list-container {
            max-height: calc(80vh - 270px);
            min-height: calc(80vh - 270px);
        }
        
        @media (min-width: 1024px) {
            .comment-list-container {
                max-height: calc(76vh - 270px);
                min-height: calc(76vh - 270px);
            }
        }
    </style>
    
    <!-- Alpine handles adding/removing comment-delete-open class -->
    <!-- Edit now uses Filament RichEditor -->
    <!-- Toolbar always visible for composer -->
    <script>
        // Function to clear undefined in composer
        function clearUndefinedInComposer(){
            const wrapper = document.querySelector('.minimal-comment-editor');
            if(!wrapper) return; 
            const pm = wrapper.querySelector('.ProseMirror');
            if(!pm) return;
            if(pm.childNodes.length === 1 && pm.textContent.trim().toLowerCase() === 'undefined'){
                pm.textContent='';
                pm.dispatchEvent(new Event('input',{bubbles:true}));
            }
        }
            
            
        // Function to prevent comments from starting with whitespace
        function preventLeadingWhitespace(event) {
            const editor = event.target;
            const text = editor.textContent || '';
            
            // If the text starts with whitespace, prevent the input
            if (text.match(/^\s/)) {
                // Remove leading whitespace
                editor.textContent = text.replace(/^\s+/, '');
                
                // Show a subtle warning
                const warning = document.createElement('div');
                warning.className = 'text-xs text-amber-600 dark:text-amber-400 mt-1';
                warning.textContent = 'Comments cannot start with spaces or newlines';
                
                // Remove any existing warning
                const existingWarning = editor.parentElement.querySelector('.text-amber-600');
                if (existingWarning) {
                    existingWarning.remove();
                }
                
                // Add warning below the editor
                editor.parentElement.appendChild(warning);
                
                // Remove warning after 3 seconds
                setTimeout(() => {
                    if (warning.parentElement) {
                        warning.remove();
                    }
                }, 3000);
            }
        }
            
        // Clear undefined in composer
        document.addEventListener('DOMContentLoaded', clearUndefinedInComposer);
        document.addEventListener('livewire:update', clearUndefinedInComposer);
        document.addEventListener('livewire:navigated', clearUndefinedInComposer);
        
        // Reset composer editor
        document.addEventListener('resetComposerEditor', () => {
            // Add a small delay to ensure DOM is ready
            setTimeout(() => {
                // Find the composer trix-editor (Filament RichEditor)
                const editor = document.querySelector('[data-composer] trix-editor');
                
                if (editor) {
                    // Clear the editor content
                    editor.textContent = '';
                    editor.innerHTML = '';
                    
                    // Trigger events to update form state
                    editor.dispatchEvent(new Event('input', { bubbles: true }));
                    editor.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }, 50);
        });
        
        // Add input event listeners to prevent leading whitespace
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const editors = document.querySelectorAll('[data-composer] trix-editor, .minimal-comment-editor .ProseMirror');
                editors.forEach(editor => {
                    editor.addEventListener('input', preventLeadingWhitespace);
                });
            }, 1000);
        });
        
        // Re-add listeners after Livewire updates
        document.addEventListener('livewire:update', function() {
            setTimeout(() => {
                const editors = document.querySelectorAll('[data-composer] trix-editor, .minimal-comment-editor .ProseMirror');
                editors.forEach(editor => {
                    editor.removeEventListener('input', preventLeadingWhitespace);
                    editor.addEventListener('input', preventLeadingWhitespace);
                });
            }, 500);
        });
    </script>

    <!-- Mention Functionality JavaScript -->
    <script>
        // Wait for Livewire to be available
        function waitForLivewire() {
            if (typeof Livewire !== 'undefined') {
                // Set up global userSelected listener immediately when Livewire is available
                setupGlobalUserSelectedListener();
                setTimeout(initializeMentions, 1000);
            } else {
                setTimeout(waitForLivewire, 100);
            }
        }
        
        // Set up global userSelected event listener
        function setupGlobalUserSelectedListener() {
            console.log('🚀 Setting up global userSelected listener');
            
            // Remove any existing listeners first to avoid duplicates
            if (window.userSelectedListenerSetup) {
                console.log('⚠️ Global listener already exists, skipping');
                return;
            }
            
            window.userSelectedListenerSetup = true;
            
            // Use both old and new Livewire event listener syntax for compatibility
            if (typeof Livewire.on === 'function') {
                Livewire.on('userSelected', function(event) {
                    // Handle both event data formats
                    const data = event.detail ? event.detail : event;
                    handleUserSelected(data);
                });
            }
            
            // Also listen for custom events on document (fallback)
            document.addEventListener('livewire:userSelected', function(event) {
                const data = event.detail;
                handleUserSelected(data);
            });
            
            // Listen for the new instant custom event (zero-delay) - with proper cleanup
            const userSelectedHandler = function(event) {
                console.log('🎯 Instant userSelected event received:', event.detail);
                handleUserSelected(event.detail);
            };
            
            window.addEventListener('userSelected', userSelectedHandler);
            
            // Store the handler for cleanup
            window.userSelectedHandler = userSelectedHandler;
            
            function handleUserSelected(data) {
                console.log('🎯 Global userSelected event received:', data);
                
                // Prevent multiple mentions from being processed
                if (mentionSelectionLock) {
                    console.log('🚫 Mention selection blocked - already processing');
                    return;
                }
                
                // Validate data
                if (!data || !data.username) {
                    console.log('❌ Invalid userSelected data:', data);
                    return;
                }
                
                // Handle special @Everyone case
                if (data.userId === '@Everyone') {
                    console.log('🎯 Processing @Everyone mention:', {
                        userId: data.userId,
                        username: data.username,
                        inputId: data.inputId,
                        timestamp: new Date().toISOString()
                    });
                    // For @Everyone, we'll let the backend handle the special case
                    // Just insert the text and let the server process it
                }
                
                // Lock mention selection
                mentionSelectionLock = true;
                
                // Reset dropdown state when user selects
                dropdownActive = false;
                atSymbolPosition = null;
                
                // Find the currently active editor
                let activeEditor = null;
                
                // IMPROVED LOGIC: Use inputId to determine which editor to prioritize
                if (data.inputId === 'editData.editingText') {
                    console.log('🎯 Looking for EDIT FORM editor based on inputId');
                    
                    // For edit forms, prioritize edit form editors first
                    const editForm = document.querySelector('.edit-form[data-edit-form="true"]');
                    if (editForm) {
                        activeEditor = editForm.querySelector('trix-editor') ||
                                     editForm.querySelector('.ProseMirror') ||
                                     editForm.querySelector('[contenteditable="true"]');
                    }
                    
                    console.log('🔍 Step 1 - Specific edit form editor:', activeEditor ? {
                        tagName: activeEditor.tagName,
                        className: activeEditor.className,
                        id: activeEditor.id
                    } : 'none found');
                    
                    // Fallback to any fi-form that's not the composer
                    if (!activeEditor) {
                        activeEditor = document.querySelector('.fi-form:not([data-composer]) trix-editor') ||
                                     document.querySelector('.fi-form:not([data-composer]) .ProseMirror') ||
                                     document.querySelector('.fi-form:not([data-composer]) [contenteditable="true"]');
                                     
                        console.log('🔍 Step 2 - General edit form editor:', activeEditor ? {
                            tagName: activeEditor.tagName,
                            className: activeEditor.className,
                            id: activeEditor.id
                        } : 'none found');
                    }
                    
                    // Last resort: check for focused editors
                    if (!activeEditor) {
                        activeEditor = document.querySelector('trix-editor:focus') || 
                                     document.querySelector('.ProseMirror:focus') ||
                                     document.querySelector('[contenteditable="true"]:focus');
                                     
                        console.log('🔍 Step 3 - Focused editor (fallback):', activeEditor ? {
                            tagName: activeEditor.tagName,
                            className: activeEditor.className,
                            id: activeEditor.id
                        } : 'none found');
                    }
                } else {
                    console.log('🎯 Looking for COMPOSER editor based on inputId or default logic');
                    
                    // For composer or unknown inputId, use original logic
                    // First try to find focused editors
                    activeEditor = document.querySelector('trix-editor:focus') || 
                                 document.querySelector('.ProseMirror:focus') ||
                                 document.querySelector('[contenteditable="true"]:focus');
                    
                    console.log('🔍 Step 1 - Focused editor:', activeEditor ? {
                        tagName: activeEditor.tagName,
                        className: activeEditor.className,
                        id: activeEditor.id
                    } : 'none found');
                    
                    // If no focused editor, try to find the composer editor
                    if (!activeEditor) {
                        activeEditor = document.querySelector('[data-composer] trix-editor') ||
                                     document.querySelector('[data-composer] .ProseMirror') ||
                                     document.querySelector('[data-composer] [contenteditable="true"]');
                                     
                        console.log('🔍 Step 2 - Composer editor:', activeEditor ? {
                            tagName: activeEditor.tagName,
                            className: activeEditor.className,
                            id: activeEditor.id
                        } : 'none found');
                    }
                    
                    // If still no editor, try to find any edit form editor
                    if (!activeEditor) {
                        // Look specifically for edit form with data-edit-form attribute
                        const editForm = document.querySelector('.edit-form[data-edit-form="true"]');
                        if (editForm) {
                            activeEditor = editForm.querySelector('trix-editor') ||
                                         editForm.querySelector('.ProseMirror') ||
                                         editForm.querySelector('[contenteditable="true"]');
                        }
                        
                        console.log('🔍 Step 3a - Specific edit form editor:', activeEditor ? {
                            tagName: activeEditor.tagName,
                            className: activeEditor.className,
                            id: activeEditor.id
                        } : 'none found');
                        
                        // Fallback to any fi-form that's not the composer
                        if (!activeEditor) {
                            activeEditor = document.querySelector('.fi-form:not([data-composer]) trix-editor') ||
                                         document.querySelector('.fi-form:not([data-composer]) .ProseMirror') ||
                                         document.querySelector('.fi-form:not([data-composer]) [contenteditable="true"]');
                                         
                            console.log('🔍 Step 3b - General edit form editor:', activeEditor ? {
                                tagName: activeEditor.tagName,
                                className: activeEditor.className,
                                id: activeEditor.id
                            } : 'none found');
                        }
                    }
                }
                
                console.log('🎯 Found active editor:', activeEditor ? {
                    tagName: activeEditor.tagName,
                    className: activeEditor.className,
                    id: activeEditor.id
                } : null);
                
                if (activeEditor && data.username) {
                    // Check if this username is already properly inserted to prevent duplicates
                    let currentText = '';
                    if (activeEditor.tagName === 'TRIX-EDITOR') {
                        currentText = activeEditor.editor.getDocument().toString();
                    } else if (activeEditor.classList.contains('ProseMirror')) {
                        currentText = activeEditor.textContent || '';
                    } else if (activeEditor.contentEditable === 'true') {
                        currentText = activeEditor.textContent || '';
                    }
                    
                    const fullMention = `@${data.username} `;
                    if (currentText.includes(fullMention)) {
                        console.log('⚠️ Username already properly inserted, skipping duplicate event');
                        // Release the selection lock
                        setTimeout(() => {
                            mentionSelectionLock = false;
                        }, 200);
                        return;
                    }
                    
                    console.log('✅ Calling insertMention with:', {
                        editor: activeEditor.tagName,
                        username: data.username,
                        currentText: currentText.substring(0, 50) + (currentText.length > 50 ? '...' : '')
                    });
                    
                    // Use the comprehensive insertMention function
                    insertMention(activeEditor, data.username);
                    
                    // Notify server of selected user id
                    if (typeof data.userId !== 'undefined') {
                        console.log('📡 Dispatching mentionSelected event:', {
                            userId: data.userId,
                            username: data.username,
                            inputId: data.inputId,
                            isEveryone: data.userId === '@Everyone',
                            timestamp: new Date().toISOString()
                        });
                        
                        Livewire.dispatch('mentionSelected', { userId: data.userId });
                        
                        console.log('✅ mentionSelected event dispatched successfully');
                    } else {
                        console.log('⚠️ No userId in data, skipping mentionSelected dispatch:', data);
                    }
                    
                    // Release the selection lock after a longer delay to prevent rapid duplicates
                    setTimeout(() => {
                        mentionSelectionLock = false;
                    }, 500); // Increased from 200ms to 500ms
                } else {
                    console.log('❌ No active editor found or no username provided', {
                        hasEditor: !!activeEditor,
                        hasUsername: !!data.username,
                        data: data
                    });
                    
                    // Release the selection lock even if no editor found
                    setTimeout(() => {
                        mentionSelectionLock = false;
                    }, 500); // Increased from 200ms to 500ms
                }
            }
        }
        // Wait for Livewire to be available
        document.addEventListener('DOMContentLoaded', function() {
            waitForLivewire();
            // Re-initialize after Livewire updates
            document.addEventListener('livewire:update', function() {
                console.log('🔄 Livewire update detected, re-initializing mentions...');
                setTimeout(initializeMentions, 500);
                
                // Also check for any edit form editors that might have appeared
                setTimeout(function() {
                    const editFormEditors = document.querySelectorAll('.edit-form[data-edit-form="true"] trix-editor, .fi-form:not([data-composer]) trix-editor, .edit-form[data-edit-form="true"] .ProseMirror, .fi-form:not([data-composer]) .ProseMirror');
                    editFormEditors.forEach(function(editor) {
                        if (!editor.dataset.mentionsInitialized) {
                            console.log('🔧 Found uninitialized edit form editor, initializing...');
                            initializeEditor(editor);
                        }
                    });
                }, 1000);
            });
            // Re-initialize after Livewire navigated
            document.addEventListener('livewire:navigated', function() {
                console.log('🔄 Livewire navigated, re-setting up mentions...');
                // Reset the listener flag so it can be re-setup
                window.userSelectedListenerSetup = false;
                setupGlobalUserSelectedListener(); // Re-setup global listener
                setTimeout(initializeMentions, 500);
            });

            // Watch for edit form being added to DOM
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        // Check if any added nodes contain an edit form
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                // Check for edit form with data-edit-form attribute
                                const specificEditForm = node.querySelector ?
                                    node.querySelector('.edit-form[data-edit-form="true"]') :
                                    null;
                                    
                                const editForm = node.querySelector ?
                                    node.querySelector('.fi-form:not([data-composer] .fi-form)') :
                                    null;
                                    
                                if (specificEditForm || editForm || 
                                   (node.classList && node.classList.contains('edit-form')) ||
                                   (node.classList && node.classList.contains('fi-form') && !node.closest('[data-composer]'))) {
                                    console.log('🔍 Edit form detected in DOM, initializing mentions...');
                                    // Wait longer for the edit form to be fully rendered
                                    setTimeout(initializeMentions, 1500);
                                }
                            }
                        });
                    }
                });
            });

            // Start observing
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            // Also listen for focus events on potential edit form editors
            document.addEventListener('focusin', function(e) {
                const target = e.target;
                if (target && (target.tagName === 'TRIX-EDITOR' || target.contentEditable === 'true' || target.getAttribute('role') === 'textbox')) {
                    // Check if this editor is in an edit form (not composer)
                    const editForm = target.closest('.fi-form');
                    const specificEditForm = target.closest('.edit-form[data-edit-form="true"]');
                    
                    if ((editForm && !editForm.closest('[data-composer]')) || specificEditForm) {
                        console.log('🎯 Edit form editor focused, initializing mentions...', {
                            tagName: target.tagName,
                            isInEditForm: !!editForm,
                            isInSpecificEditForm: !!specificEditForm,
                            alreadyInitialized: !!target.dataset.mentionsInitialized
                        });
                        
                        // Small delay to ensure the editor is fully ready
                        setTimeout(function() {
                            if (!target.dataset.mentionsInitialized) {
                                initializeEditor(target);
                            }
                        }, 100);
                    }
                }
            });

            // Listen for edit button clicks to prepare for edit form appearing
            document.addEventListener('click', function(e) {
                const editButton = e.target.closest('button[wire\\:click*="startEdit"]');
                if (editButton) {
                    console.log('🔧 Edit button clicked, preparing for edit form...');
                    // Wait a bit for the edit form to appear, then initialize
                    setTimeout(function() {
                        const editFormEditor = document.querySelector('.edit-form[data-edit-form="true"] trix-editor, .edit-form[data-edit-form="true"] .ProseMirror');
                        if (editFormEditor && !editFormEditor.dataset.mentionsInitialized) {
                            console.log('🎯 Edit form appeared, initializing editor...');
                            initializeEditor(editFormEditor);
                        }
                    }, 1500); // Wait for Livewire to update and render the edit form
                }
            });


        });
        // Initialize mentions
        function initializeMentions() {
            // For Filament RichEditor components, we need to wait for Trix to be ready
            const composerEditor = document.querySelector('[data-composer] trix-editor');
            if (composerEditor && !composerEditor.dataset.mentionsInitialized) {
                initializeTrixEditor(composerEditor);
                return;
            }

            // Check for edit form editors
            const editEditor = document.querySelector('.fi-form:not([data-composer]) trix-editor');
            if (editEditor && !editEditor.dataset.mentionsInitialized) {
                initializeTrixEditor(editEditor);
                return;
            }
            
            // Check specifically for edit forms with data-edit-form attribute
            const specificEditEditor = document.querySelector('.edit-form[data-edit-form="true"] trix-editor');
            if (specificEditEditor && !specificEditEditor.dataset.mentionsInitialized) {
                initializeTrixEditor(specificEditEditor);
                return;
            }

            // If no editors found yet, wait and try again
            setTimeout(() => {
                const anyTrixEditor = document.querySelector('trix-editor');
                if (anyTrixEditor && !anyTrixEditor.dataset.mentionsInitialized) {
                    initializeTrixEditor(anyTrixEditor);
                }
            }, 500);
        }

        // Specialized initialization for Trix editors
        function initializeTrixEditor(trixEditor) {
            if (trixEditor.dataset.mentionsInitialized) {
                return;
            }
            
            trixEditor.dataset.mentionsInitialized = 'true';

            // Wait for Trix to be fully ready
            if (trixEditor.editor) {
                setupTrixMentions(trixEditor);
            } else {
                trixEditor.addEventListener('trix-initialize', function() {
                    setupTrixMentions(trixEditor);
                });
            }
        }

        // Setup mention functionality for Trix editor
        function setupTrixMentions(trixEditor) {
            // Listen for text changes in Trix
            trixEditor.addEventListener('trix-change', function(e) {
                if (!insertingMention) {
                    handleTrixMentionDetection(trixEditor);
                }
            });

            // Listen for cursor position changes
            trixEditor.addEventListener('trix-selection-change', function(e) {
                if (dropdownActive && !insertingMention) {
                    handleTrixMentionDetection(trixEditor);
                }
            });

            // Handle keyboard events for dropdown navigation
            trixEditor.addEventListener('keydown', function(e) {
                if (!dropdownActive) return;

                if (e.key === 'Escape') {
                    e.preventDefault();
                    dropdownActive = false;
                    atSymbolPosition = null;
                    Livewire.dispatch('hideMentionDropdown');
                }
                // Note: Arrow keys and Enter are now handled by the dropdown's global listener
                // This allows the editor to maintain focus while still enabling navigation
            });
        }

        // Handle mention detection in Trix editor
        function handleTrixMentionDetection(trixEditor) {
            if (!trixEditor.editor) return;

            const document = trixEditor.editor.getDocument();
            const range = trixEditor.editor.getSelectedRange();
            const text = document.toString();
            const cursorPosition = range[0];
            const beforeCursor = text.substring(0, cursorPosition);

            // Check for @ mention pattern - improved regex to capture search term properly
            let atMatch = beforeCursor.match(/(?:^|\s)@(\w*)$/);
            
            if (!atMatch && beforeCursor.endsWith('@')) {
                atMatch = ['@', '']; // New @ symbol with empty search term
            }

            if (!atMatch) {
                if (dropdownActive) {
                    dropdownActive = false;
                    atSymbolPosition = null;
                    Livewire.dispatch('hideMentionDropdown');
                }
                return;
            }

            // Clean the search term - remove @ symbol if present
            let searchTerm = atMatch[1] || '';
            searchTerm = searchTerm.replace(/^@+/, ''); // Remove leading @ symbols
            
            const atIndex = beforeCursor.lastIndexOf('@');

            console.log('🔍 Trix mention detection:', { 
                beforeCursor: beforeCursor.substring(Math.max(0, beforeCursor.length - 20)),
                rawMatch: atMatch,
                cleanedSearchTerm: searchTerm
            });

            if (!dropdownActive) {
                showTrixMentionDropdown(trixEditor, searchTerm, atIndex);
            } else {
                updateTrixMentionDropdown(trixEditor, searchTerm);
            }
        }

        // Show mention dropdown for Trix editor
        function showTrixMentionDropdown(trixEditor, searchTerm, atIndex) {
            try {
                // Get position using Trix's built-in method
                const rect = trixEditor.editor.getClientRectAtPosition(atIndex);
                
                if (rect && rect.left > 0 && rect.top > 0) {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                    
                    const finalPosition = {
                        left: rect.left + scrollLeft,
                        top: rect.bottom + scrollTop
                    };

                    atSymbolPosition = finalPosition;
                    dropdownActive = true;

                    // Determine input ID based on editor context
                    const inputId = trixEditor.closest('[data-composer]') ? 'composerData.newComment' : 'editData.editingText';
                    
                    console.log('� Dispatching Trix showMentionDropdown:', { 
                        inputId, 
                        searchTerm, 
                        x: finalPosition.left, 
                        y: finalPosition.top 
                    });

                    Livewire.dispatch('showMentionDropdown', {
                        inputId: inputId,
                        searchTerm: searchTerm,
                        x: finalPosition.left,
                        y: finalPosition.top
                    });
                } else {
                    // Fallback to editor position
                    const editorRect = trixEditor.getBoundingClientRect();
                    if (editorRect) {
                        showTrixMentionDropdownFallback(trixEditor, searchTerm, editorRect);
                    }
                }
            } catch (error) {
                // Fallback
                const editorRect = trixEditor.getBoundingClientRect();
                if (editorRect) {
                    showTrixMentionDropdownFallback(trixEditor, searchTerm, editorRect);
                }
            }
        }

        // Fallback method to show dropdown
        function showTrixMentionDropdownFallback(trixEditor, searchTerm, editorRect) {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
            
            const finalPosition = {
                left: editorRect.left + scrollLeft,
                top: editorRect.bottom + scrollTop + 5
            };

            atSymbolPosition = finalPosition;
            dropdownActive = true;

            const inputId = trixEditor.closest('[data-composer]') ? 'composerData.newComment' : 'editData.editingText';
            
            console.log('📡 Dispatching Trix showMentionDropdown (fallback):', { 
                inputId, 
                searchTerm, 
                x: finalPosition.left, 
                y: finalPosition.top 
            });

            Livewire.dispatch('showMentionDropdown', {
                inputId: inputId,
                searchTerm: searchTerm,
                x: finalPosition.left,
                y: finalPosition.top
            });
        }

        // Update mention dropdown for Trix editor
        function updateTrixMentionDropdown(trixEditor, searchTerm) {
            const inputId = trixEditor.closest('[data-composer]') ? 'composerData.newComment' : 'editData.editingText';
            
            console.log('📡 Dispatching Trix showMentionDropdown (update):', { 
                inputId, 
                searchTerm, 
                x: atSymbolPosition.left, 
                y: atSymbolPosition.top 
            });

            Livewire.dispatch('showMentionDropdown', {
                inputId: inputId,
                searchTerm: searchTerm,
                x: atSymbolPosition.left,
                y: atSymbolPosition.top
            });
        }
        // Find the edit form editor specifically
        function findEditEditor() {
            // Look for Trix editor in edit form (when editing a comment)
            // Try different selectors to find the edit form
            let editForm = document.querySelector('.edit-form[data-edit-form="true"]');

            if (!editForm) {
                // Try alternative selectors
                editForm = document.querySelector('.edit-form');
            }

            if (!editForm) {
                // Try to find any fi-form that's not the composer
                const allForms = document.querySelectorAll('.fi-form');
                for (let form of allForms) {
                    if (!form.closest('[data-composer]')) {
                        editForm = form;
                        break;
                    }
                }
            }

            if (editForm) {
                // Try multiple ways to find the editor within the edit form
                const richEditor = editForm.querySelector('.fi-fo-rich-editor, .fi-fo-rich-editor-container');
                if (richEditor) {
                    let editor = richEditor.querySelector('trix-editor');
                    if (editor) {
                        return editor;
                    }

                    editor = richEditor.querySelector('.ProseMirror, [contenteditable="true"], [role="textbox"]');
                    if (editor) {
                        return editor;
                    }
                }
                
                // Direct search in edit form
                let editor = editForm.querySelector('trix-editor');
                if (editor) {
                    return editor;
                }
                
                editor = editForm.querySelector('.ProseMirror, [contenteditable="true"], [role="textbox"]');
                if (editor) {
                    return editor;
                }
            }

            return null;
        }

        // Find the composer editor
        function findEditor() {
            let editor = null;

            // Look for Trix editor in minimal-comment-editor class (composer)
            const minimalCommentEditor = document.querySelector('.minimal-comment-editor');
            if (minimalCommentEditor) {
                editor = minimalCommentEditor.querySelector('trix-editor');
                if (editor) {
                    return editor;
                }

                editor = minimalCommentEditor.querySelector('.ProseMirror, [contenteditable="true"], [role="textbox"]');
                if (editor) {
                    return editor;
                }
            }

            // Look for Trix editor in comment composer
            const commentComposer = document.querySelector('[data-composer]');
            if (commentComposer) {
                const richEditor = commentComposer.querySelector('.fi-fo-rich-editor, .fi-fo-rich-editor-container');
                if (richEditor) {
                    editor = richEditor.querySelector('trix-editor');
                    if (editor) {
                        return editor;
                    }

                    editor = richEditor.querySelector('.ProseMirror, [contenteditable="true"], [role="textbox"]');
                    if (editor) {
                        return editor;
                    }
                }
            }

            return null;
        }
        // Wait for the editor to be available
        function waitForEditor() {
            let attempts = 0;
            const maxAttempts = 100;
            
            function checkForEditor() {
                attempts++;
                
                const editor = findEditor();
                if (editor) {
                    initializeEditor(editor);
                    return;
                }
                
                if (attempts < maxAttempts) {
                    setTimeout(checkForEditor, 100);
                }
            }
            
            setTimeout(checkForEditor, 100);
        }

        function initializeEditor(editor) {
            console.log('🚀 Initializing editor:', { 
                tagName: editor.tagName, 
                id: editor.id, 
                className: editor.className,
                alreadyInitialized: !!editor.dataset.mentionsInitialized
            });
            
            if (editor.dataset.mentionsInitialized) {
                console.log('⚠️ Editor already initialized, skipping');
                return;
            }

            editor.dataset.mentionsInitialized = 'true';
            
            // Add event listeners
            editor.addEventListener('input', function(e) {
                console.log('⌨️ Input event fired on editor');
                handleMentionInput(e, editor);
            });
            
            // Search term updates are now handled automatically in handleMentionInput
            // No need for separate keyup handler
            editor.addEventListener('keyup', function(e) {
                // Keep this for any future keyup-specific logic if needed
            });
            
            editor.addEventListener('keydown', function(e) {
                handleMentionKeydown(e, editor);
            });
            
            console.log('✅ Editor initialized with mention listeners');

            // Insert mention into Trix editor
            function insertTrixMention(trixEditor, username) {
                if (!trixEditor.editor) return;

                try {
                    insertingMention = true;
                    
                    const document = trixEditor.editor.getDocument();
                    const range = trixEditor.editor.getSelectedRange();
                    const text = document.toString();
                    const cursorPosition = range[0];
                    const beforeCursor = text.substring(0, cursorPosition);
                    
                    // Find the @ symbol position
                    const atIndex = beforeCursor.lastIndexOf('@');
                    if (atIndex !== -1) {
                        // Select from @ to current cursor position
                        const selectionRange = [atIndex, cursorPosition];
                        trixEditor.editor.setSelectedRange(selectionRange);
                        
                        // Insert the mention
                        const mentionText = `@${username} `;
                        trixEditor.editor.insertString(mentionText);
                    }
                    
                    setTimeout(() => {
                        insertingMention = false;
                    }, 100);
                } catch (error) {
                    insertingMention = false;
                }
            }
            
            // Listen for hideMentionDropdown event
            Livewire.on('hideMentionDropdown', function() {
                // Dropdown hidden - reset all state
                dropdownActive = false;
                atSymbolPosition = null;
            });

            // Listen for showMentionDropdown event - keep editor focused for typing
            Livewire.on('showMentionDropdown', function() {
                // Mark dropdown as active when it appears
                dropdownActive = true;
                
                // Ensure the editor stays focused for typing
                setTimeout(() => {
                    const activeEditor = document.querySelector('trix-editor:focus') || 
                                       document.querySelector('.ProseMirror:focus') ||
                                       document.querySelector('[contenteditable="true"]:focus');
                    if (activeEditor) {
                        activeEditor.focus();
                    }
                }, 50);
                
                // Note: Initial selection is now handled by the dropdown's Alpine.js component
                // This provides instant visual feedback without Livewire round-trips
            });
            
            // Search term updates are now handled directly in handleMentionInputDebounced
            // This function is kept for backward compatibility but simplified
            function updateSearchTerm(editor) {
                // The main logic is now in handleMentionInputDebounced
                // This function can be called for manual updates if needed
                if (dropdownActive) {
                    handleMentionInputDebounced({ type: 'manual' }, editor);
                }
            }
        }
        // Add a flag to prevent mention detection when inserting
        let insertingMention = false;
        let atSymbolPosition = null;
        let dropdownActive = false;
        let mentionSelectionLock = false;
        // Add debouncing to prevent multiple rapid calls
        let mentionInputTimeout = null;
        // Handle mention input
        function handleMentionInput(e, editor) {
            if (insertingMention) {
                return;
            }
            
            // Clear any existing timeout
            if (mentionInputTimeout) {
                clearTimeout(mentionInputTimeout);
            }
            
            // Debounce the input handling to prevent multiple rapid calls
            mentionInputTimeout = setTimeout(() => {
                handleMentionInputDebounced(e, editor);
            }, 10); // 10ms debounce
        }
        // Handle mention input debounced
        function handleMentionInputDebounced(e, editor) {
            console.log('🔍 handleMentionInputDebounced called', { editor: editor.tagName, editorId: editor.id });
            
            const text = editor.textContent || '';
            const cursorPosition = getCursorPosition(editor);
            const beforeCursor = text.substring(0, cursorPosition);
            
            console.log('📝 Text analysis:', { 
                text: text.substring(0, 50) + (text.length > 50 ? '...' : ''), 
                cursorPosition, 
                beforeCursor: beforeCursor.substring(Math.max(0, beforeCursor.length - 20)) 
            });
            
            // ENHANCED LOGIC: Handle both new @ and search updates with better pattern matching
            
            // 1. Check if we have a valid @ pattern - handle both @ at end and @ followed by space
            let atMatch = beforeCursor.match(/(?:^|\s)@(\w*)$/);
            
            // If no match and cursor is right after @, check for @ at end of beforeCursor
            if (!atMatch && beforeCursor.endsWith('@')) {
                atMatch = beforeCursor.match(/(?:^|\s)@$/);
                if (atMatch) {
                    // This is a new @ symbol, treat as empty search term
                    atMatch = ['@', '']; // Simulate match with empty search term
                    console.log('🎯 Found @ at end, treating as new mention');
                }
            }
            
            console.log('🔎 Pattern matching result:', { atMatch, dropdownActive });
            // If no match, hide the dropdown
            if (!atMatch) {
                console.log('❌ No @ pattern found, hiding dropdown if active');
                // No valid @ pattern - hide dropdown if active
                if (dropdownActive) {
                    dropdownActive = false;
                    atSymbolPosition = null;
                    Livewire.dispatch('hideMentionDropdown');
                }
                return;
            }
            
            // 2. We have a valid @ pattern - check if we need to show or update dropdown
            const searchTerm = atMatch[1] || '';
            const atIndex = beforeCursor.lastIndexOf('@');
            
            console.log('✅ Valid @ pattern found:', { searchTerm, atIndex, dropdownActive });
            
            if (!dropdownActive) {
                console.log('🚀 Showing new dropdown...');
                // Show new dropdown
                const atPosition = getCaretCoordinatesAtIndex(editor, atIndex);

                // Try to get position, with fallbacks
                let finalPosition = atPosition;
                
                console.log('📍 Initial position:', atPosition);

                if (!finalPosition || finalPosition.left === 0 || finalPosition.top === 0) {
                    console.log('🔄 Using fallback position...');
                    // Fallback 1: Try to get editor's bounding rect
                    const editorRect = editor.getBoundingClientRect();
                    if (editorRect) {
                        finalPosition = {
                            left: editorRect.left,
                            top: editorRect.bottom + 5
                        };
                    }
                }

                if (finalPosition && finalPosition.left !== 0 && finalPosition.top !== 0) {
                    atSymbolPosition = finalPosition;
                    dropdownActive = true;

                    // Determine the input ID based on the editor element
                    const inputId = editor.closest('[data-composer]') ? 'composerData.newComment' : 'editData.editingText';
                    
                    console.log('📡 Dispatching showMentionDropdown:', { 
                        inputId, 
                        searchTerm, 
                        x: finalPosition.left, 
                        y: finalPosition.top 
                    });
                    
                    Livewire.dispatch('showMentionDropdown', {
                        inputId: inputId,
                        searchTerm: searchTerm,
                        x: finalPosition.left,
                        y: finalPosition.top
                    });
                } else {
                    console.log('❌ Could not determine position for dropdown');
                }
            } else {
                console.log('🔄 Updating existing dropdown...');
                // Update existing dropdown with new search term
                // Determine the input ID based on the editor element
                const inputId = editor.closest('[data-composer]') ? 'composerData.newComment' : 'editData.editingText';
                
                console.log('📡 Dispatching showMentionDropdown (update):', { 
                    inputId, 
                    searchTerm, 
                    x: atSymbolPosition.left, 
                    y: atSymbolPosition.top 
                });
                
                Livewire.dispatch('showMentionDropdown', {
                    inputId: inputId,
                    searchTerm: searchTerm,
                    x: atSymbolPosition.left,
                    y: atSymbolPosition.top
                });
            }
        }
        function handleMentionKeydown(e, editor) {
            // Always handle Shift+Enter to prevent bold formatting issues
            if (e.key === 'Enter' && e.shiftKey) {
                // Let the default behavior happen but clean up any potential bold formatting
                // We'll handle this in the sanitizeHtml function in PHP
                
                // Add a small delay to allow the editor to update
                setTimeout(() => {
                    // Ensure no bold formatting is applied
                    if (editor.classList.contains('ProseMirror')) {
                        // For ProseMirror, we can't directly manipulate the editor's internal state
                        // So we'll rely on the sanitizeHtml function in PHP
                    } else if (editor.tagName === 'TRIX-EDITOR' && editor.editor) {
                        // For Trix, we can try to deactivate bold formatting
                        try {
                            if (typeof editor.editor.deactivateAttribute === 'function') {
                                editor.editor.deactivateAttribute('bold');
                            }
                        } catch (error) {
                            console.error('Error deactivating bold after Shift+Enter:', error);
                        }
                    }
                }, 10);
                
                return; // Let the default behavior happen
            }
            
            // Check if dropdown is visible before handling navigation keys
            const dropdown = document.querySelector('.user-mention-dropdown');
            const isDropdownVisible = dropdown !== null;
            
            if (!isDropdownVisible) {
                return; // Don't interfere with normal typing if no dropdown
            }
            
            // Only handle specific keys when dropdown is open - let typing pass through
            if (e.key === 'Escape') {
                e.preventDefault();
                dropdownActive = false;
                atSymbolPosition = null;
                Livewire.dispatch('hideMentionDropdown');
            }
            // Note: Arrow keys and Enter are now handled by the dropdown's Alpine.js component
            // All other keys (letters, numbers, etc.) pass through to the editor for typing
        }
        // Insert mention
        function insertMention(editor, username) {
            if (!username || username === 'undefined') {
                console.log('❌ Invalid username for insertion:', username);
                return;
            }
            
            // Prevent duplicate insertions
            if (insertingMention) {
                console.log('🚫 Insertion blocked - already inserting mention');
                return;
            }
            
            console.log('🎯 Starting insertMention:', { 
                editor: editor.tagName, 
                username: username,
                editorClass: editor.className 
            });
            
            insertingMention = true;
            
            // Avoid mutating Livewire internals to keep DOM stable
            // Helpers to safely replace a text range inside a contenteditable without rewriting outer HTML
            function getTextNodeAndOffset(root, position) {
                let currentPos = 0;
                const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null, false);
                let node;
                while ((node = walker.nextNode())) {
                    const len = node.textContent.length;
                    if (currentPos + len >= position) {
                        return { node, offset: Math.max(0, position - currentPos) };
                    }
                    currentPos += len;
                }
                return null;
            }
            // Replace text in editor
            function replaceTextInEditor(root, startPos, endPos, replacementText) {
                try {
                    const start = getTextNodeAndOffset(root, startPos);
                    const end = getTextNodeAndOffset(root, endPos);
                    if (!start || !end) return false;
                    const range = document.createRange();
                    range.setStart(start.node, start.offset);
                    range.setEnd(end.node, end.offset);
                    range.deleteContents();
                    const textNode = document.createTextNode(replacementText);
                    range.insertNode(textNode);
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    const after = document.createRange();
                    after.setStart(textNode, textNode.textContent.length);
                    after.collapse(true);
                    selection.addRange(after);
                    return true;
                } catch (e) {
                    console.error('replaceTextInEditor failed:', e);
                    return false;
                }
            }
            
            // Check for ProseMirror editor first (Filament Rich Editor)
            if (editor.classList.contains('ProseMirror')) {
                try {
                    // Get current text content and cursor position
                    const text = editor.textContent || '';
                    const selection = window.getSelection();
                    let cursorPosition = 0;
                    
                    // Get the actual cursor position in the text content
                    if (selection.rangeCount > 0) {
                        const range = selection.getRangeAt(0);
                        const preCaretRange = range.cloneRange();
                        preCaretRange.selectNodeContents(editor);
                        preCaretRange.setEnd(range.startContainer, range.startOffset);
                        cursorPosition = preCaretRange.toString().length;
                        
                        const beforeCursor = text.substring(0, cursorPosition);
                        
                        console.log('🔍 insertMention debug:', {
                            text: text,
                            cursorPosition: cursorPosition,
                            beforeCursor: beforeCursor,
                            username: username
                        });
                        
                        // Find the @ symbol in the current text - look for the last @ in the entire text
                        const atIndex = text.lastIndexOf('@');

                        // If the @ symbol is found, insert the mention
                        if (atIndex !== -1) {
                            // Find where the partial mention ends by looking at text from @ position
                            const textFromAt = text.substring(atIndex);
                            const spaceIndex = textFromAt.indexOf(' ');
                            const newlineIndex = textFromAt.indexOf('\n');
                            
                            // Find the first occurrence of space or newline (whichever comes first)
                            let endIndex = textFromAt.length; // Default to end of text
                            if (spaceIndex !== -1) endIndex = Math.min(endIndex, spaceIndex);
                            if (newlineIndex !== -1) endIndex = Math.min(endIndex, newlineIndex);
                            
                            console.log('🔍 Replacement calculation:', {
                                atIndex: atIndex,
                                textFromAt: textFromAt,
                                spaceIndex: spaceIndex,
                                newlineIndex: newlineIndex,
                                endIndex: endIndex
                            });
                            
                            // Create new text: replace @ and partial text with @username
                            const beforeAt = text.substring(0, atIndex);
                            const afterPartial = text.substring(atIndex + endIndex);
                            
                            console.log('🔍 Text parts:', {
                                beforeAt: beforeAt,
                                partialToReplace: text.substring(atIndex, atIndex + endIndex),
                                afterPartial: afterPartial
                            });
                            // Handle usernames with spaces by keeping the spaces intact
                            const formattedUsername = username;
                            // Use plain text for mentions to avoid HTML issues
                            const mentionHtml = '@' + formattedUsername + ' ';
                            const newText = beforeAt + mentionHtml + afterPartial;
                            
                            // Replace the content safely (avoid full innerHTML reset)
                            try {
                                const range = document.createRange();
                                range.selectNodeContents(editor);
                                range.deleteContents();
                                const temp = document.createElement('div');
                                temp.innerHTML = newText;
                                while (temp.firstChild) {
                                    editor.appendChild(temp.firstChild);
                                }
                            } catch (e) {
                                editor.textContent = beforeAt + '@' + username + ' ' + afterPartial;
                            }
                            
                            // Calculate cursor position after the mention
                            setTimeout(() => {
                                try {
                                    // Calculate position after the username and space
                                    const mentionLength = username.length + 2; // @ + username + space
                                    const newPosition = atIndex + mentionLength;
                                    
                                    // Set the cursor position after the mention
                                    const range = document.createRange();
                                    const selection = window.getSelection();
                                    
                                    // Find the text node where the cursor should be positioned
                                    const textNode = findTextNodeAtPosition(editor, newPosition);
                                    if (textNode) {
                                        // Calculate offset within the text node
                                        const offset = calculateOffsetInNode(editor, textNode, newPosition);
                                        range.setStart(textNode, offset);
                                        range.collapse(true);
                                        selection.removeAllRanges();
                                        selection.addRange(range);
                                    } else {
                                        // Fallback: position at end
                                        range.selectNodeContents(editor);
                                        range.collapse(false);
                                        selection.removeAllRanges();
                                        selection.addRange(range);
                                    }

                                    
                                    // Helper function to find text node at position
                                    function findTextNodeAtPosition(rootNode, position) {
                                        let currentPos = 0;
                                        let foundNode = null;
                                        
                                        function traverse(node) {
                                            if (foundNode) return;
                                            
                                            if (node.nodeType === Node.TEXT_NODE) {
                                                const nodeLength = node.textContent.length;
                                                if (currentPos <= position && position <= currentPos + nodeLength) {
                                                    foundNode = node;
                                                    return;
                                                }
                                                currentPos += nodeLength;
                                            } else {
                                                for (let i = 0; i < node.childNodes.length; i++) {
                                                    traverse(node.childNodes[i]);
                                                }
                                            }
                                        }
                                        
                                        traverse(rootNode);
                                        return foundNode;
                                    }
                                    
                                    // Helper function to calculate offset in a text node
                                    function calculateOffsetInNode(rootNode, targetNode, position) {
                                        let currentPos = 0;
                                        let offset = 0;
                                        
                                        function traverse(node) {
                                            if (node === targetNode) {
                                                offset = position - currentPos;
                                                return true;
                                            }
                                            
                                            if (node.nodeType === Node.TEXT_NODE) {
                                                currentPos += node.textContent.length;
                                            } else {
                                                for (let i = 0; i < node.childNodes.length; i++) {
                                                    if (traverse(node.childNodes[i])) {
                                                        return true;
                                                    }
                                                }
                                            }
                                            return false;
                                        }
                                        
                                        traverse(rootNode);
                                        return offset;
                                    }
                                    
                                    // Focus editor
                                    editor.focus();
                                    
                                    setTimeout(() => {
                                        editor.dispatchEvent(new Event('input', { bubbles: true }));
                                    }, 10);
                                } catch (error) {
                                    console.error('ProseMirror cursor positioning error:', error);
                                    editor.focus();
                                    editor.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            }, 100); // Increased timeout
                        }
                    }
                } catch (error) {
                    console.error('Error inserting mention in ProseMirror:', error);
                }
            } else if (editor.tagName === 'TRIX-EDITOR') {
                try {
                    const trixEditor = editor.editor;
                    
                    // Get current text content
                    const currentText = trixEditor.getDocument().toString();
                    
                    console.log('🔍 Trix insertMention debug:', {
                        currentText: currentText,
                        username: username
                    });
                    
                    // Find the @ symbol in the current text
                    const atIndex = currentText.lastIndexOf('@');
                    
                    if (atIndex !== -1) {
                        // Find where the partial mention ends by looking at text from @ position
                        const textFromAt = currentText.substring(atIndex);
                        const spaceIndex = textFromAt.indexOf(' ');
                        const newlineIndex = textFromAt.indexOf('\n');
                        
                        // Find the first occurrence of space or newline (whichever comes first)
                        let endIndex = textFromAt.length; // Default to end of text
                        if (spaceIndex !== -1) endIndex = Math.min(endIndex, spaceIndex);
                        if (newlineIndex !== -1) endIndex = Math.min(endIndex, newlineIndex);
                        
                        console.log('🔍 Trix replacement calculation:', {
                            atIndex: atIndex,
                            textFromAt: textFromAt,
                            endIndex: endIndex,
                            partialToReplace: currentText.substring(atIndex, atIndex + endIndex)
                        });
                        
                        // Create new text: replace @ and partial text with @username
                        const beforeAt = currentText.substring(0, atIndex);
                        const afterPartial = currentText.substring(atIndex + endIndex);
                        const newText = beforeAt + '@' + username + ' ' + afterPartial;
                                // Use Trix's native selection and insertion API instead of loadHTML
                                // Set selection to the @ symbol and the partial text after it
                                const startPosition = atIndex;
                                const endPosition = atIndex + endIndex;
                                trixEditor.setSelectedRange([startPosition, endPosition]);
                                // Since Trix strips HTML, use native Trix formatting instead
                                // Get the position before insertion to calculate the correct range
                                const beforeInsertionRange = trixEditor.getSelectedRange();
                                const insertionStartPos = beforeInsertionRange[0];
                                
                                // First insert the @username text - handle usernames with spaces by wrapping in quotes if needed
                                const formattedUsername = username.includes(' ') ? username : username;
                                trixEditor.insertString('@' + formattedUsername);
                                
                                // Calculate the correct range for the inserted text
                                const mentionText = '@' + formattedUsername;
                                const mentionStart = insertionStartPos;
                                const mentionEnd = insertionStartPos + mentionText.length;
                                
                                // Apply Trix formatting to make it stand out
                                // Select the text we just inserted
                                trixEditor.setSelectedRange([mentionStart, mentionEnd]);
                                
                                // Verify the selection
                                const verifyRange = trixEditor.getSelectedRange();
                                
                                // Do not apply any formatting to mentions - keep them as plain text
                                
                                // Move cursor to end of mention before inserting space
                                trixEditor.setSelectedRange([mentionEnd, mentionEnd]);

                                // Now insert a space after the mention
                                trixEditor.insertString(' ');
                                
                                // Get current cursor position (should be after the inserted content)
                                const currentSelection = trixEditor.getSelectedRange();
                                
                                // Verify the content was inserted correctly
                        const newTextContent = trixEditor.getDocument().toString();
                        
                                // Ensure the editor is focused
                                editor.focus();
                    }
                } catch (error) {
                    console.error('Error inserting mention in Trix editor:', error);
                }
            } else {
                // Fallback for contenteditable elements
                const text = editor.textContent || '';
                const atIndex = text.lastIndexOf('@');
                
                console.log('🔍 Fallback insertMention debug:', {
                    text: text,
                    atIndex: atIndex,
                    username: username
                });
                
                if (atIndex !== -1) {
                    const beforeAt = text.substring(0, atIndex);
                    const textFromAt = text.substring(atIndex);
                    
                    // Find where the partial mention ends by looking for space or newline
                    const spaceIndex = textFromAt.indexOf(' ');
                    const newlineIndex = textFromAt.indexOf('\n');
                    
                    // Find the first occurrence of space or newline (whichever comes first)
                    let endIndex = textFromAt.length; // Default to end of text
                    if (spaceIndex !== -1) endIndex = Math.min(endIndex, spaceIndex);
                    if (newlineIndex !== -1) endIndex = Math.min(endIndex, newlineIndex);
                    
                    const afterPartial = text.substring(atIndex + endIndex);
                    
                    console.log('🔍 Fallback replacement calculation:', {
                        textFromAt: textFromAt,
                        endIndex: endIndex,
                        partialToReplace: text.substring(atIndex, atIndex + endIndex),
                        beforeAt: beforeAt,
                        afterPartial: afterPartial
                    });
                    
                    // Create new text (plain text replacement to avoid DOM corruption)
                    // Handle usernames with spaces by keeping the spaces intact
                    const formattedUsername = username;
                    const newText = beforeAt + '@' + formattedUsername + ' ' + afterPartial;
                    editor.textContent = newText;
                    
                    // Set cursor position after the inserted username and space
                    setTimeout(() => {
                        try {
                            // Get the new text content after HTML insertion
                            const newTextContent = editor.textContent || '';
                            
                            // Find the position after the inserted username
                            // Look for the username in the new text content
                            const usernameIndex = newTextContent.indexOf('@' + username);
                            if (usernameIndex !== -1) {
                                // Position cursor after the username and space
                                const cursorPosition = usernameIndex + username.length + 1 + 1; // @ + username + space
                                setCursorPosition(editor, cursorPosition);
                            } else {
                                // Fallback: position at end of text
                                setCursorPosition(editor, newTextContent.length);
                            }
                            
                            // Ensure the editor is focused
                            editor.focus();
                            
                            setTimeout(() => {
                                editor.dispatchEvent(new Event('input', { bubbles: true }));
                            }, 10);
                        } catch (error) {
                            console.error('Error setting cursor position in contenteditable:', error);
                            // Still trigger input event even if cursor positioning fails
                            editor.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }, 50);
                }
            }
            
            // Reset flag and restore Livewire functionality after insertion
            setTimeout(() => {
                insertingMention = false;

            }, 500); // Longer delay to ensure cursor positioning is stable
        }

        // Get cursor position
        function getCursorPosition(element) {
            // Handle Trix editor specifically
            if (element.tagName === 'TRIX-EDITOR' && element.editor) {
                const selectedRange = element.editor.getSelectedRange();
                return selectedRange ? selectedRange[0] : element.editor.getDocument().getLength();
            }
            
            // Handle other editors with DOM selection
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                if (element.contains(range.startContainer)) {
                    // Calculate position more accurately for contenteditable
                    let position = 0;
                    const walker = document.createTreeWalker(
                        element,
                        NodeFilter.SHOW_TEXT,
                        null,
                        false
                    );
                    
                    let node;
                    while (node = walker.nextNode()) {
                        if (node === range.startContainer) {
                            return position + range.startOffset;
                        }
                        position += node.textContent.length;
                    }
                }
            }
            return element.textContent.length;
        }
        // Set cursor position
        function setCursorPosition(element, position) {
            const range = document.createRange();
            const selection = window.getSelection();
            
            try {
                // Find the text node to place cursor in
                let textNode = null;
                let currentPos = 0;
                // Find the text node at the position
                function findTextNodeAtPosition(node) {
                    if (node.nodeType === Node.TEXT_NODE) {
                        if (currentPos + node.textContent.length >= position) {
                            textNode = node;
                            return true;
                        }
                        currentPos += node.textContent.length;
                    } else {
                        for (let child of node.childNodes) {
                            if (findTextNodeAtPosition(child)) {
                                return true;
                            }
                        }
                    }
                    return false;
                }
                // Find the text node at the position
                findTextNodeAtPosition(element);
                // If the text node is found, set the cursor position
                if (textNode) {
                    const offset = position - (currentPos - textNode.textContent.length);
                    const safeOffset = Math.min(Math.max(0, offset), textNode.textContent.length);
                    range.setStart(textNode, safeOffset);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                } else if (element.firstChild) {
                    // Fallback to end of first child
                    if (element.firstChild.nodeType === Node.TEXT_NODE) {
                        range.setStart(element.firstChild, element.firstChild.textContent.length);
                    } else {
                        range.setStartAfter(element.firstChild);
                    }
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            } catch (error) {
                console.error('Error setting cursor position:', error);
                // Fallback: just focus the element
                element.focus();
            }
        }
        // Get caret coordinates
        function getCaretCoordinates(element, position) {
            // For Trix editor, we need to get the actual cursor position
            if (element.tagName === 'TRIX-EDITOR') {
                try {
                    // Get the current selection/cursor position in Trix
                    const selection = element.editor.getSelectedRange();
                    if (selection && selection.length > 0) {
                        // Get the position of the current selection
                        const rect = element.editor.getClientRectAtPosition(selection[0]);
                        if (rect) {
                            return {
                                left: rect.left,
                                top: rect.bottom
                            };
                        }
                    }
                } catch (error) {
                    // Fall through to element position
                }
            }
            // For contenteditable elements, try to get cursor position
            if (element.isContentEditable) {
                try {
                    const range = document.createRange();
                    const selection = window.getSelection();
                    
                    if (element.firstChild) {
                        range.setStart(element.firstChild, Math.min(position, element.firstChild.length));
                        range.collapse(true);
                        selection.removeAllRanges();
                        selection.addRange(range);
                        
                        const rect = range.getBoundingClientRect();
                        if (rect && rect.left !== 0 && rect.top !== 0) {
                            return {
                                left: rect.left,
                                top: rect.bottom
                            };
                        }
                    }
                } catch (error) {
                    // Fall through to element position
                }
            }
            // Fallback to element position with better positioning
            try {
                const rect = element.getBoundingClientRect();
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                
                return {
                    left: rect.left + scrollLeft,
                    top: rect.bottom + scrollTop
                };
            } catch (error) {
                // Last resort: return null to indicate failure
                return null;
            }
        }
        // Get caret coordinates at index
        function getCaretCoordinatesAtIndex(element, index) {
            // For Trix editor, get position at specific character index
            if (element.tagName === 'TRIX-EDITOR') {
                try {
                    const rect = element.editor.getClientRectAtPosition(index);
                    if (rect) {
                        return {
                            left: rect.left,
                            top: rect.bottom
                        };
                    }
                } catch (error) {
                    // Fall through to contenteditable logic
                }
            }
            // For contenteditable elements, create range at specific index
            if (element.isContentEditable) {
                try {
                    const range = document.createRange();
                    const selection = window.getSelection();
                    
                    if (element.firstChild) {
                        const textNode = element.firstChild;
                        const safeIndex = Math.min(index, textNode.length);
                        
                        range.setStart(textNode, safeIndex);
                        range.collapse(true);
                        selection.removeAllRanges();
                        selection.addRange(range);
                        
                        const rect = range.getBoundingClientRect();
                        if (rect && rect.left !== 0 && rect.top !== 0) {
                            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                            
                            return {
                                left: rect.left + scrollLeft,
                                top: rect.bottom + scrollTop
                            };
                        }
                    }
                } catch (error) {
                    // Fall through to element position
                }
            }
            // Fallback: use getCaretCoordinates with the index as position
            return getCaretCoordinates(element, index);
        }
        
        // Livewire Comments Emoji Picker Functions
        let livewireEmojiPickerInitialized = false;
        let currentLivewireCommentId = null;
        let livewireCommentEmojiStates = {}; // Track emoji state per comment
        
        function toggleLivewireEmojiPicker(commentId, button) {
            const emojiPickerContainer = document.getElementById("livewire-comments-emoji-picker-container");
            const emojiPicker = document.getElementById("livewire-comments-emoji-picker");
            
            if (!emojiPickerContainer || !emojiPicker) {
                console.error('Livewire comments emoji picker elements not found');
                return;
            }
            
            currentLivewireCommentId = commentId;
            
            if (emojiPickerContainer.classList.contains("hidden")) {
                // Position the emoji picker to the left of the comment
                const buttonRect = button.getBoundingClientRect();
                const commentElement = button.closest('[wire\\:key^="comment-"]');
                
                if (commentElement) {
                    const commentRect = commentElement.getBoundingClientRect();
                    
                    // Position to the left of the comment with some spacing
                    const leftPosition = commentRect.left - 430; // 420px width + 10px spacing
                    
                    // Position vertically centered with the comment
                    const topPosition = commentRect.top + (commentRect.height / 2) - 200; // Center vertically
                    
                    // Ensure it doesn't go off-screen
                    const finalLeftPosition = Math.max(20, Math.min(leftPosition, window.innerWidth - 420));
                    const finalTopPosition = Math.max(20, Math.min(topPosition, window.innerHeight - 420));
                    
                    emojiPickerContainer.style.left = finalLeftPosition + "px";
                    emojiPickerContainer.style.top = finalTopPosition + "px";
                    
                    emojiPickerContainer.classList.remove("hidden");
                    
                    // Add animation
                    emojiPickerContainer.style.opacity = "0";
                    emojiPickerContainer.style.transform = "translateX(-20px) scale(0.95)";
                    requestAnimationFrame(() => {
                        emojiPickerContainer.style.transition = "opacity 0.2s ease, transform 0.2s ease";
                        emojiPickerContainer.style.opacity = "1";
                        emojiPickerContainer.style.transform = "translateX(0) scale(1)";
                    });
                    
                    // Focus the emoji picker
                    emojiPicker.focus();
                }
            } else {
                // Close the picker
                emojiPickerContainer.style.transition = "opacity 0.2s ease, transform 0.2s ease";
                emojiPickerContainer.style.opacity = "0";
                emojiPickerContainer.style.transform = "translateX(-20px) scale(0.95)";
                setTimeout(() => {
                    emojiPickerContainer.classList.add("hidden");
                }, 200);
            }
        }
        
        function initializeLivewireEmojiPicker() {
            if (livewireEmojiPickerInitialized) {
                return;
            }
            
            const emojiPicker = document.getElementById("livewire-comments-emoji-picker");
            if (!emojiPicker) {
                return;
            }
            
            livewireEmojiPickerInitialized = true;
            
            // Configure emoji picker
            emojiPicker.addEventListener("emoji-click", (event) => {
                const emoji = event.detail.unicode;
                addLivewireEmojiReaction(emoji);
            });
            
            // Set emoji picker properties
            emojiPicker.style.setProperty("--category-emoji-size", "1.5rem");
            emojiPicker.style.setProperty("--emoji-size", "1.5rem");
            emojiPicker.style.setProperty("--num-columns", "8");
            emojiPicker.style.setProperty("--border-radius", "0.5rem");
            
            // Close picker when clicking outside
            document.addEventListener('click', (e) => {
                const container = document.getElementById("livewire-comments-emoji-picker-container");
                const button = e.target.closest('.emoji-reaction-btn-livewire');
                
                if (container && !container.contains(e.target) && !button) {
                    if (!container.classList.contains("hidden")) {
                        container.style.transition = "opacity 0.2s ease, transform 0.2s ease";
                        container.style.opacity = "0";
                        container.style.transform = "translateX(-20px) scale(0.95)";
                        setTimeout(() => {
                            container.classList.add("hidden");
                        }, 200);
                    }
                }
            });
        }
        
        function addLivewireEmojiReaction(emoji) {
            if (!currentLivewireCommentId) {
                console.error('No comment ID set for Livewire emoji reaction');
                return;
            }
            
            console.log('Adding Livewire emoji reaction:', emoji, 'to comment:', currentLivewireCommentId);
            
            // Capture the comment ID before making the request
            const commentId = currentLivewireCommentId;
            
            // Send emoji reaction to server
            const csrfToken = document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token') || 
                             document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                             document.querySelector('input[name="_token"]')?.value || '';
            
            fetch(`/comments/${commentId}/emoji`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ emoji: emoji })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store the emoji state for this comment
                    livewireCommentEmojiStates[commentId] = {
                        emoji: emoji,
                        username: data.username,
                        created_at: data.created_at
                    };
                    
                    // Find the comment element and replace the button with the emoji
                    const commentElement = document.querySelector(`[wire\\:key="comment-${commentId}"]`);
                    if (commentElement) {
                        const emojiContainer = commentElement.querySelector('.emoji-container-livewire');
                        if (emojiContainer) {
                            // Replace the button with the emoji
                            emojiContainer.innerHTML = `
                                <button 
                                    type="button"
                                    class="emoji-display-btn-livewire p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-900/20 focus:outline-none focus:ring-1 focus:ring-gray-500/40 transition-all duration-200"
                                    data-comment-id="${commentId}"
                                    title="${data.username} - ${data.created_at}"
                                >
                                    <span class="text-lg">${emoji}</span>
                                </button>
                            `;
                            
                            // Add click handler to remove the emoji
                            const emojiDisplayBtn = emojiContainer.querySelector('.emoji-display-btn-livewire');
                            const commentIdToRemove = commentId; // Capture the comment ID
                            emojiDisplayBtn.addEventListener('click', function() {
                                removeLivewireEmojiReaction(commentIdToRemove);
                            });
                        }
                    }
                } else {
                    console.error('Failed to save Livewire emoji reaction:', data.message);
                    alert('Failed to save emoji reaction: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error saving Livewire emoji reaction:', error);
                alert('Error saving emoji reaction: ' + error.message);
            });
            
            // Close the emoji picker
            const container = document.getElementById("livewire-comments-emoji-picker-container");
            if (container) {
                container.style.transition = "opacity 0.2s ease, transform 0.2s ease";
                container.style.opacity = "0";
                container.style.transform = "translateX(-20px) scale(0.95)";
                setTimeout(() => {
                    container.classList.add("hidden");
                }, 200);
            }
            
            // Reset current comment ID
            currentLivewireCommentId = null;
        }
        
        function removeLivewireEmojiReaction(commentId) {
            console.log('removeLivewireEmojiReaction called with commentId:', commentId);
            console.log('Current Livewire emoji states:', livewireCommentEmojiStates);
            
            // Send remove request to server
            const csrfToken = document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token') || 
                             document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                             document.querySelector('input[name="_token"]')?.value || '';
            
            fetch(`/comments/${commentId}/emoji`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the emoji state for this comment
                    delete livewireCommentEmojiStates[commentId];
                    
                    // Find the comment element and restore the picker button
                    const commentElement = document.querySelector(`[wire\\:key="comment-${commentId}"]`);
                    console.log('Found Livewire comment element:', commentElement);
                    
                    if (commentElement) {
                        const emojiContainer = commentElement.querySelector('.emoji-container-livewire');
                        console.log('Found Livewire emoji container:', emojiContainer);
                        
                        if (emojiContainer) {
                            // Restore the original picker button
                            emojiContainer.innerHTML = `
                                <button 
                                    type="button"
                                    class="emoji-reaction-btn-livewire p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-900/20 focus:outline-none focus:ring-1 focus:ring-gray-500/40 transition-all duration-200"
                                    data-comment-id="${commentId}"
                                    title="Add emoji reaction"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            `;
                            console.log('Restored Livewire picker button for comment:', commentId);
                        }
                    }
                } else {
                    console.error('Failed to remove Livewire emoji reaction:', data.message);
                    alert('Failed to remove emoji reaction: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error removing Livewire emoji reaction:', error);
                alert('Error removing emoji reaction: ' + error.message);
            });
        }
        
        // Load existing emoji reactions for all Livewire comments
        function loadExistingLivewireEmojiReactions() {
            // Get all comment IDs from the page
            const commentElements = document.querySelectorAll('[wire\\:key^="comment-"]');
            const commentIds = Array.from(commentElements).map(el => {
                const wireKey = el.getAttribute('wire:key');
                return wireKey ? wireKey.replace('comment-', '') : null;
            }).filter(id => id !== null);
            
            if (commentIds.length === 0) {
                console.log('No Livewire comments found to load emoji reactions for');
                return;
            }
            
            console.log('Loading Livewire emoji reactions for comments:', commentIds);
            
            // Send batch request to get emoji reactions
            const csrfToken = document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token') || 
                             document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                             document.querySelector('input[name="_token"]')?.value || '';
            
            fetch('/comments/emoji/batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ comment_ids: commentIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Loaded Livewire emoji reactions:', data.reactions);
                    
                    // Update UI for each comment with an emoji reaction
                    Object.keys(data.reactions).forEach(commentId => {
                        const reactionData = data.reactions[commentId];
                        if (reactionData) {
                            // Store in local state
                            livewireCommentEmojiStates[commentId] = {
                                emoji: reactionData.emoji,
                                username: reactionData.username,
                                created_at: reactionData.created_at
                            };
                            
                            // Update UI
                            const commentElement = document.querySelector(`[wire\\:key="comment-${commentId}"]`);
                            if (commentElement) {
                                const emojiContainer = commentElement.querySelector('.emoji-container-livewire');
                                if (emojiContainer) {
                                    // Replace the button with the emoji
                                    emojiContainer.innerHTML = `
                                        <button 
                                            type="button"
                                            class="emoji-display-btn-livewire p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-900/20 focus:outline-none focus:ring-1 focus:ring-gray-500/40 transition-all duration-200"
                                            data-comment-id="${commentId}"
                                            title="${reactionData.username} - ${reactionData.created_at}"
                                        >
                                            <span class="text-lg">${reactionData.emoji}</span>
                                        </button>
                                    `;
                                    
                                    // Add click handler to remove the emoji
                                    const emojiDisplayBtn = emojiContainer.querySelector('.emoji-display-btn-livewire');
                                    const commentIdToRemove = commentId; // Capture the comment ID
                                    emojiDisplayBtn.addEventListener('click', function() {
                                        removeLivewireEmojiReaction(commentIdToRemove);
                                    });
                                }
                            }
                        }
                    });
                } else {
                    console.error('Failed to load Livewire emoji reactions:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading Livewire emoji reactions:', error);
            });
        }
        
        // Initialize Livewire emoji picker when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeLivewireEmojiPicker();
            // Load existing emoji reactions after a short delay to ensure DOM is fully loaded
            setTimeout(loadExistingLivewireEmojiReactions, 100);
        });
        
        // Handle Livewire emoji reaction button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('emoji-reaction-btn-livewire') || e.target.closest('.emoji-reaction-btn-livewire')) {
                const btn = e.target.classList.contains('emoji-reaction-btn-livewire') ? e.target : e.target.closest('.emoji-reaction-btn-livewire');
                const commentId = btn.getAttribute('data-comment-id');
                toggleLivewireEmojiPicker(commentId, btn);
            }
        });
    </script>

    <!-- Notification Handler Script -->
    <script>
        function notificationHandler() {
            return {
                notifications: [],

                init() {
                    // Listen for notification events from child components
                    this.$el.addEventListener('show-notification', (event) => {
                        this.showNotification(event.detail.message, event.detail.type);
                    });
                },

                showNotification(message, type = 'info') {
                    const id = Date.now() + Math.random();
                    const notification = {
                        id: id,
                        message: message,
                        type: type,
                        visible: true
                    };

                    this.notifications.push(notification);

                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        this.removeNotification(id);
                    }, 5000);
                },

                removeNotification(id) {
                    const index = this.notifications.findIndex(n => n.id === id);
                    if (index !== -1) {
                        this.notifications[index].visible = false;
                        setTimeout(() => {
                            this.notifications.splice(index, 1);
                        }, 200); // Wait for transition to complete
                    }
                }
            }
        }
    </script>

    <!-- Include the User Mention Dropdown Component -->
    <div wire:ignore>
        @livewire('user-mention-dropdown')
    </div>
    
    <!-- Floating Emoji Picker Container for Livewire Comments -->
    <div id="livewire-comments-emoji-picker-container" class="fixed hidden z-[11]">
        <emoji-picker id="livewire-comments-emoji-picker"></emoji-picker>
    </div>
    
    <!-- Emoji Picker Element -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    
    <!-- Emoji Picker Theme CSS -->
    @vite('resources/css/emoji-picker-theme.css')
</div>

