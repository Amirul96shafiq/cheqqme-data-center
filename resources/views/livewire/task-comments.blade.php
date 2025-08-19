<div class="flex flex-col flex-1 h-full min-h-0 rounded-xl bg-white dark:bg-gray-900">

    <!-- Composer (Top) -->
    <div class="px-0 pt-0 pb-5 bg-white dark:bg-gray-900" data-composer>
        <div class="space-y-3">
            <div class="fi-form">
                {{ $this->composerForm }}
            </div>
            @error('newComment') <p class="text-xs text-danger-600">{{ $message }}</p> @enderror
            <button wire:click="addComment" wire:loading.attr="disabled" wire:target="addComment,saveEdit,performDelete,deleteComment" type="button" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500/50 disabled:opacity-50">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                <span wire:loading.remove wire:target="addComment,saveEdit,performDelete,deleteComment">{{ __('comments.composer.send') }}</span>
                <span wire:loading wire:target="addComment,saveEdit,performDelete,deleteComment">{{ __('comments.composer.saving') }}</span>
            </button>
        </div>
    </div>

    <!-- Comments List (scroll area) -->
    <div class="flex-1 min-h-0 px-0 pb-0">
    <div class="px-4 py-4 text-sm overflow-y-auto custom-thin-scroll h-full" data-comment-list style="max-height:calc(68vh - 270px);">
        <div class="space-y-6">
            @forelse($this->comments as $comment)
                <div class="group relative flex gap-3" wire:key="comment-{{ $comment->id }}">
                    <div class="flex-shrink-0">
					@php
						$avatarPath = $comment->user->avatar ?? null;
						$avatarUrl = $avatarPath ? \Storage::url($avatarPath) : null;
					@endphp
					@if($avatarUrl)
						<img src="{{ $avatarUrl }}" alt="{{ $comment->user->username ?? __('comments.meta.user_fallback') }}" class="w-10 h-10 rounded-full object-cover ring-1 ring-white/20 dark:ring-gray-800 shadow-sm" loading="lazy">
					@else
						@php
							$defaultAvatarUrl = (new \Filament\AvatarProviders\UiAvatarsProvider())->get($comment->user);
						@endphp
						<img src="{{ $defaultAvatarUrl }}" alt="{{ $comment->user->username ?? __('comments.meta.user_fallback') }}" class="w-10 h-10 rounded-full object-cover ring-1 ring-white/20 dark:ring-gray-800 shadow-sm" loading="lazy">
					@endif
                    </div>
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
                            @if(auth()->id() === $comment->user_id)
                                <div class="flex items-center gap-1">
                                    @if($this->editingId !== $comment->id)
                                        <button type="button" wire:click="startEdit({{ $comment->id }})" class="p-1.5 rounded-md text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/40" title="{{ __('comments.buttons.edit') }}">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button type="button" wire:click="confirmDelete({{ $comment->id }})" class="p-1.5 rounded-md text-gray-400 focus:outline-none focus:ring-2 focus:ring-danger-500/40" title="{{ __('comments.buttons.delete') }}">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="mt-2">
                            @if($this->editingId === $comment->id)
                                <div class="space-y-2">
                                    <div class="fi-form">{{ $this->editForm }}</div>
                                    <div class="flex items-center gap-2">
                                        <button wire:click="saveEdit" type="button" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/50">{{ __('comments.buttons.save') }}</button>
                                        <button wire:click="cancelEdit" type="button" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200">{{ __('comments.buttons.cancel') }}</button>
                                    </div>
                                </div>
                            @else
                                <div class="prose prose-xs dark:prose-invert max-w-none leading-snug text-[13px] text-gray-700 dark:text-gray-300 break-words">{!! $comment->comment !!}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-2 py-8 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">{{ __('comments.list.none') }}</p>
                </div>
            @endforelse
        </div>
        @if($this->totalComments > 0)
            <div class="mt-3 text-[10px] text-gray-400 text-center">{{ __('comments.list.showing', ['shown' => $this->comments->count(), 'total' => $this->totalComments]) }}</div>
        @endif
        @if($this->totalComments > $visibleCount)
            @php $remaining = $this->totalComments - $visibleCount; @endphp
            <div class="mt-2">
                <button wire:click="showMore" type="button" class="w-full text-xs font-medium px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500/40">{{ __('comments.list.show_more', ['count' => ($remaining < 5 ? $remaining : 5)]) }}</button>
            </div>
        @endif
        </div>
    </div>
    @if($confirmingDeleteId)
        <!-- Elevated z-index to ensure overlay sits above form action buttons -->
        <div
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
            <div class="comment-delete-modal-backdrop absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75" wire:click="cancelDelete" aria-hidden="true"></div>
            <div role="dialog" aria-modal="true" aria-labelledby="delete-comment-heading" class="comment-delete-modal fi-modal-window relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">
                <button type="button" wire:click="cancelDelete" class="fi-modal-close-btn absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900" aria-label="{{ __('comments.modal.delete.close') }}">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <div class="flex flex-col items-center text-center">
                    <div class="mb-5 flex items-center justify-center">
                        <div class="p-3 rounded-full bg-danger-100 text-danger-600 dark:bg-danger-500/20 dark:text-danger-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </div>
                    </div>
                    <h2 id="delete-comment-heading" class="fi-modal-heading text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('comments.modal.delete.title') }}</h2>
                    <p class="fi-modal-description mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ __('comments.modal.delete.description') }}</p>
                    <div class="mt-6 flex w-full items-stretch gap-3">
                        <button data-modal-initial type="button" wire:click="cancelDelete" class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">{{ __('comments.modal.delete.cancel') }}</button>
                        <button type="button" wire:click="performDelete" class="fi-btn fi-color-danger flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-danger-600 text-white hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-danger-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-danger-600 dark:hover:bg-danger-500 dark:focus:ring-offset-gray-900">{{ __('comments.modal.delete.confirm') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Single backdrop already blocks clicks; extra blocker removed -->
    @endif
    @if(!$confirmingDeleteId)
        <script>
            // Ensure the helper class is cleared when modal not present
            document.documentElement.classList.remove('comment-delete-open');
        </script>
    @endif
        <style>
            .custom-thin-scroll::-webkit-scrollbar { width: 6px; }
            .custom-thin-scroll::-webkit-scrollbar-track { background: transparent; }
            .custom-thin-scroll::-webkit-scrollbar-thumb { background: rgba(100,116,139,.35); border-radius: 3px; }
            .dark .custom-thin-scroll::-webkit-scrollbar-thumb { background: rgba(148,163,184,.30); }
            .custom-thin-scroll:hover::-webkit-scrollbar-thumb { background: rgba(100,116,139,.55); }
            .dark .custom-thin-scroll:hover::-webkit-scrollbar-thumb { background: rgba(148,163,184,.50); }
            .custom-thin-scroll { scrollbar-width: thin; scrollbar-color: rgba(148,163,184,.35) transparent; }
            .dark .custom-thin-scroll { scrollbar-color: rgba(148,163,184,.35) transparent; }
            .comment-username { font-size: 14px; font-weight: 700; }
            .comment-meta { font-size: 11px; line-height: 1rem; }
            /* Scroll lock only when modal open */
            .comment-delete-open body { overflow: hidden; }
            /* Hide underlying Edit Task modal submit/cancel buttons while delete confirmation is open */
            .comment-delete-open .fi-modal-window:not(.comment-delete-modal) .fi-modal-footer,
            .comment-delete-open .fi-modal-window:not(.comment-delete-modal) .fi-modal-actions,
            .comment-delete-open .fi-modal-window:not(.comment-delete-modal) footer button {
                visibility: hidden !important;
            }
            /* Minimal single-row Filament RichEditor (keep toolbar visible) */
            .minimal-comment-editor .fi-fo-rich-editor-toolbar { padding: 0.15rem 0.25rem; gap: .25rem; display:flex; }
            .minimal-comment-editor .fi-fo-rich-editor-toolbar button { height: 1.75rem; width: 1.75rem; }
            .minimal-comment-editor .fi-fo-rich-editor-container { padding: 0 !important; }
            .minimal-comment-editor .fi-fo-rich-editor,
            .minimal-comment-editor .fi-fo-rich-editor-container,
            .minimal-comment-editor .fi-fo-rich-editor-container .ProseMirror { min-height: 2rem !important; max-height: 2rem !important; }
            .minimal-comment-editor .fi-fo-rich-editor-container .ProseMirror { overflow: hidden !important; white-space: nowrap; line-height: 1.1rem; padding: .25rem .6rem !important; }
            .minimal-comment-editor .fi-fo-rich-editor-container .ProseMirror p { margin: 0; display:inline; }
            .minimal-comment-editor .fi-fo-rich-editor-container .ProseMirror p + p { display:inline; }
            .minimal-comment-editor [data-placeholder]::before { top: 4px !important; }
            /* Force custom placeholder text to override any stray literal content flicker */
            .minimal-comment-editor [data-placeholder]::before { content: @json(__('comments.composer.placeholder')); }
            .minimal-comment-editor .fi-fo-rich-editor { border-radius: .5rem; }
            .minimal-comment-editor .fi-fo-rich-editor:focus-within .fi-fo-rich-editor-container .ProseMirror { white-space: normal; overflow:auto; max-height: 12rem !important; }
            .minimal-comment-editor .fi-fo-rich-editor:focus-within { box-shadow: 0 0 0 2px rgba(59,130,246,.4); }
            /* Comment content blockquote styling */
            .prose.prose-xs blockquote { font-weight: normal !important; font-style: italic; border-left: 3px solid rgba(148,163,184,.6); padding-left: .75rem; margin: .5rem 0; background: linear-gradient(to right, rgba(148,163,184,.10), rgba(148,163,184,0)); border-radius: 0 .375rem .375rem 0; }
            .dark .prose.prose-xs blockquote { border-left-color: rgba(100,116,139,.6); background: linear-gradient(to right, rgba(51,65,85,.40), rgba(51,65,85,0)); }
            .prose.prose-xs blockquote p { font-weight: inherit !important; }
    </style>
    <!-- Alpine handles adding/removing comment-delete-open class; no global pointer-events lock -->
    <!-- Custom composer script removed; using Filament RichEditor -->
        <!-- Edit now uses Filament RichEditor; Alpine editor script removed -->
        <!-- Toolbar always visible for composer; watcher script removed -->
        <script>
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
            
            document.addEventListener('DOMContentLoaded', clearUndefinedInComposer);
            document.addEventListener('livewire:update', clearUndefinedInComposer);
            document.addEventListener('livewire:navigated', clearUndefinedInComposer);
            
            document.addEventListener('resetComposerEditor', () => {
                const wrapper = document.querySelector('.minimal-comment-editor');
                const pm = wrapper?.querySelector('.ProseMirror');
                if (pm) {
                    pm.innerHTML='';
                    pm.dispatchEvent(new Event('input',{bubbles:true}));
                }
            });
            
            // Add input event listeners to prevent leading whitespace
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    const editors = document.querySelectorAll('.minimal-comment-editor .ProseMirror, [data-composer] .ProseMirror');
                    editors.forEach(editor => {
                        editor.addEventListener('input', preventLeadingWhitespace);
                    });
                }, 1000);
            });
            
            // Re-add listeners after Livewire updates
            document.addEventListener('livewire:update', function() {
                setTimeout(() => {
                    const editors = document.querySelectorAll('.minimal-comment-editor .ProseMirror, [data-composer] .ProseMirror');
                    editors.forEach(editor => {
                        editor.removeEventListener('input', preventLeadingWhitespace);
                        editor.addEventListener('input', preventLeadingWhitespace);
                    });
                }, 500);
            });
        </script>

    <!-- User Mention Dropdown Component -->
    <livewire:user-mention-dropdown />

    <!-- Mention Functionality JavaScript -->
    <script>
        // Wait for Livewire to be available
        function waitForLivewire() {
            if (typeof Livewire !== 'undefined') {
                setTimeout(initializeMentions, 1000);
            } else {
                setTimeout(waitForLivewire, 100);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            waitForLivewire();
            
            // Re-initialize after Livewire updates
            document.addEventListener('livewire:update', function() {
                setTimeout(initializeMentions, 500);
            });
            
            document.addEventListener('livewire:navigated', function() {
                setTimeout(initializeMentions, 500);
            });
        });

        function initializeMentions() {
            let editor = findEditor();
            
            if (editor) {
                initializeEditor(editor);
                return;
            }
            
            waitForEditor();
        }

        function findEditor() {
            let editor = null;
            
            // Look for Trix editor in minimal-comment-editor class
            const minimalCommentEditor = document.querySelector('.minimal-comment-editor');
            if (minimalCommentEditor) {
                editor = minimalCommentEditor.querySelector('trix-editor');
                if (editor) return editor;
                
                editor = minimalCommentEditor.querySelector('.ProseMirror, [contenteditable="true"], [role="textbox"]');
                if (editor) return editor;
            }
            
            // Look for Trix editor in comment composer
            const commentComposer = document.querySelector('[data-composer]');
            if (commentComposer) {
                const richEditor = commentComposer.querySelector('.fi-fo-rich-editor, .fi-fo-rich-editor-container');
                if (richEditor) {
                    editor = richEditor.querySelector('trix-editor');
                    if (editor) return editor;
                    
                    editor = richEditor.querySelector('.ProseMirror, [contenteditable="true"], [role="textbox"]');
                    if (editor) return editor;
                }
            }
            
            return null;
        }

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
            if (editor.dataset.mentionsInitialized) {
                return;
            }
            
            editor.dataset.mentionsInitialized = 'true';
            
            // Add event listeners
            editor.addEventListener('input', function(e) {
                handleMentionInput(e, editor);
            });
            
            editor.addEventListener('keyup', function(e) {
                if (e.key === '@') {
                    setTimeout(() => {
                        handleMentionInput(e, editor);
                    }, 10);
                }
            });
            
            editor.addEventListener('keydown', function(e) {
                handleMentionKeydown(e, editor);
            });
            
            // Listen for user selection from dropdown
            Livewire.on('userSelected', function(data) {
                if (data.inputId === 'composerData.newComment' || data.inputId === editor.id) {
                    insertMention(editor, data.username);
                } else {
                    insertMention(editor, data.username);
                }
                // Editor should already be focused - no need to restore focus
                // User can continue typing naturally
            });
            
            // Listen for hideMentionDropdown event
            Livewire.on('hideMentionDropdown', function() {
                // Dropdown hidden - reset position when hidden
                atSymbolPosition = null;
                // Editor should already be focused - no need to restore focus
                // User can continue typing naturally
            });

            // Listen for showMentionDropdown event - keep editor focused for typing
            Livewire.on('showMentionDropdown', function() {
                // Keep editor focused so user can continue typing to search
                // The dropdown will appear for visual feedback and keyboard navigation
                // No need to blur or hide cursor - let user type naturally
                
                                 // Reset client-side navigation state when dropdown appears
                 currentSelectedIndex = 0;
                 // Apply initial selection to first item immediately
                 const dropdown = document.querySelector('.user-mention-dropdown');
                 if (dropdown) {
                     const userItems = dropdown.querySelectorAll('.user-mention-item');
                     if (userItems.length > 0) {
                         // Remove any existing selections first
                         userItems.forEach(item => {
                             item.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
                         });
                         // Apply selection to first item
                         userItems[0].classList.add('bg-blue-50', 'dark:bg-blue-900/20');
                     }
                 }
            });

            // Navigation is now handled client-side for better performance
            Livewire.on('selectCurrentUser', function() {
                // Selection handled by Livewire component
            });
        }

        // Add a flag to prevent mention detection when inserting
        let insertingMention = false;
        let atSymbolPosition = null; // Store the position where @ was typed

        function handleMentionInput(e, editor) {
            if (insertingMention) {
                return;
            }
            
            const text = editor.textContent || '';
            const cursorPosition = getCursorPosition(editor);
            const beforeCursor = text.substring(0, cursorPosition);
            
            // Check for @ symbol
            const atMatch = beforeCursor.match(/@(\w*)$/);
            if (atMatch) {
                const searchTerm = atMatch[1];
                
                // Calculate position only when @ is first typed (searchTerm is empty)
                // or when we don't have a stored position yet
                if (!atSymbolPosition || searchTerm === '') {
                    // Find the position of the @ symbol in the text
                    const atIndex = beforeCursor.lastIndexOf('@');
                    const atPosition = getCaretCoordinatesAtIndex(editor, atIndex);
                    
                    if (atPosition && atPosition.left !== 0 && atPosition.top !== 0) {
                        atSymbolPosition = atPosition;
                        
                        // Show mention dropdown at @ symbol position
                        Livewire.dispatch('showMentionDropdown', {
                            inputId: 'composerData.newComment',
                            searchTerm: searchTerm,
                            x: atPosition.left,
                            y: atPosition.top
                        });
                        
                        // Set dropdown as focusable for keyboard navigation
                        // But keep editor focused so user can continue typing
                        setTimeout(() => {
                            const dropdown = document.querySelector('.user-mention-dropdown');
                            if (dropdown) {
                                dropdown.setAttribute('tabindex', '0');
                                // Don't blur editor - keep it focused for typing
                                // Dropdown will handle keyboard navigation when needed
                            }
                        }, 100);
                    }
                } else {
                    // Just update the search term, keep position static
                    Livewire.dispatch('showMentionDropdown', {
                        inputId: 'composerData.newComment',
                        searchTerm: searchTerm,
                        x: atSymbolPosition.left,
                        y: atSymbolPosition.top
                    });
                }
            } else {
                // Hide mention dropdown if no @ symbol and reset position
                atSymbolPosition = null;
                Livewire.dispatch('hideMentionDropdown');
            }
        }

        // Client-side navigation state
        let currentSelectedIndex = 0;
        
        function handleMentionKeydown(e, editor) {
            // Check if dropdown is visible before handling navigation keys
            const dropdown = document.querySelector('.user-mention-dropdown');
            // Since the dropdown uses Livewire conditional rendering, we just need to check if the element exists
            const isDropdownVisible = dropdown !== null;
            
            if (!isDropdownVisible) {
                return; // Don't interfere with normal typing if no dropdown
            }
            
            // Handle keyboard navigation when dropdown is open
            if (e.key === 'Escape') {
                e.preventDefault();
                console.log('Dispatching hideMentionDropdown');
                Livewire.dispatch('hideMentionDropdown');
                // Keep editor focused - no need to restore focus
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                navigateUp();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                navigateDown();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                console.log('Dispatching selectCurrentUser');
                Livewire.dispatch('selectCurrentUser');
            }
            // For all other keys (typing), let the editor handle them normally
        }
        
        function navigateUp() {
            const dropdown = document.querySelector('.user-mention-dropdown');
            if (!dropdown) return;
            
            const userItems = dropdown.querySelectorAll('.user-mention-item');
            if (userItems.length === 0) return;
            
            // Calculate new index with wrapping
            const newIndex = currentSelectedIndex > 0 ? currentSelectedIndex - 1 : userItems.length - 1;
            
            // Only update if index actually changed
            if (newIndex !== currentSelectedIndex) {
                // Remove previous selection (if exists)
                if (currentSelectedIndex >= 0 && currentSelectedIndex < userItems.length) {
                    userItems[currentSelectedIndex].classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
                }
                
                // Update index
                currentSelectedIndex = newIndex;
                
                // Apply new selection
                if (currentSelectedIndex >= 0 && currentSelectedIndex < userItems.length) {
                    userItems[currentSelectedIndex].classList.add('bg-blue-50', 'dark:bg-blue-900/20');
                    
                    // Simple, instant scroll to keep item visible
                    userItems[currentSelectedIndex].scrollIntoView({ 
                        block: 'nearest', 
                        behavior: 'instant' 
                    });
                }
                
                // Update Livewire component state immediately (no debounce for navigation)
                Livewire.dispatch('updateSelectedIndex', { index: currentSelectedIndex });
            }
        }
        
        function navigateDown() {
            const dropdown = document.querySelector('.user-mention-dropdown');
            if (!dropdown) return;
            
            const userItems = dropdown.querySelectorAll('.user-mention-item');
            if (userItems.length === 0) return;
            
            // Calculate new index with wrapping
            const newIndex = currentSelectedIndex < userItems.length - 1 ? currentSelectedIndex + 1 : 0;
            
            // Only update if index actually changed
            if (newIndex !== currentSelectedIndex) {
                // Remove previous selection (if exists)
                if (currentSelectedIndex >= 0 && currentSelectedIndex < userItems.length) {
                    userItems[currentSelectedIndex].classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
                }
                
                // Update index
                currentSelectedIndex = newIndex;
                
                // Apply new selection
                if (currentSelectedIndex >= 0 && currentSelectedIndex < userItems.length) {
                    userItems[currentSelectedIndex].classList.add('bg-blue-50', 'dark:bg-blue-900/20');
                    
                    // Simple, instant scroll to keep item visible
                    userItems[currentSelectedIndex].scrollIntoView({ 
                        block: 'nearest', 
                        behavior: 'instant' 
                    });
                }
                
                // Update Livewire component state immediately (no debounce for navigation)
                Livewire.dispatch('updateSelectedIndex', { index: currentSelectedIndex });
            }
        }

        function insertMention(editor, username) {
            if (!username || username === 'undefined') {
                return;
            }
            
            insertingMention = true;
            
            // For Trix editor, use the Trix API to insert text
            if (editor.tagName === 'TRIX-EDITOR') {
                try {
                    const trixEditor = editor.editor;
                    
                    // Get current text content
                    const currentText = trixEditor.getDocument().toString();
                    
                    // Find the @ symbol in the current text
                    const atIndex = currentText.lastIndexOf('@');
                    
                    if (atIndex !== -1) {
                        // Find where the @ symbol ends (at space or end of text)
                        const afterAt = currentText.substring(atIndex);
                        const spaceIndex = afterAt.indexOf(' ');
                        const endIndex = spaceIndex !== -1 ? spaceIndex : afterAt.length;
                        
                        // Create new text: replace @ and partial text with @username
                        const beforeAt = currentText.substring(0, atIndex);
                        const afterPartial = currentText.substring(atIndex + endIndex);
                        const newText = beforeAt + '<mark class="bg-yellow-200 dark:bg-yellow-800 px-1 rounded">@' + username + '</mark> ' + afterPartial;
                        
                        // Replace the entire content with HTML
                        trixEditor.loadHTML(newText);
                        
                        // Set cursor position after the inserted username
                        const newTextContent = trixEditor.getDocument().toString();
                        const mentionEndIndex = newTextContent.indexOf(username, atIndex) + username.length + 1;
                        
                        trixEditor.setSelectedRange([mentionEndIndex, mentionEndIndex]);
                        
                        // Trigger input event to update Livewire
                        editor.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                } catch (error) {
                    console.error('Error inserting mention in Trix editor:', error);
                }
            } else {
                // Fallback for contenteditable elements
                const text = editor.textContent || '';
                const atIndex = text.lastIndexOf('@');
                
                if (atIndex !== -1) {
                    const beforeCursor = text.substring(0, atIndex);
                    const afterCursor = text.substring(atIndex);
                    
                    // Find where the @ symbol ends (at space or end of text)
                    const spaceIndex = afterCursor.indexOf(' ');
                    const endIndex = spaceIndex !== -1 ? spaceIndex : afterCursor.length;
                    const afterPartial = afterCursor.substring(endIndex);
                    
                    // Create new text with highlighting
                    const newText = beforeCursor + '@' + username + ' ' + afterPartial;
                    editor.innerHTML = newText.replace('@' + username, '<mark class="bg-yellow-200 dark:bg-yellow-800 px-1 rounded">@' + username + '</mark>');
                    
                    // Set cursor position after the inserted username
                    const newTextContent = editor.textContent || '';
                    const mentionEndIndex = newTextContent.indexOf(username, atIndex) + username.length + 1;
                    setCursorPosition(editor, mentionEndIndex);
                    
                    // Trigger input event to update Livewire
                    editor.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
            
            // Reset flag after insertion
            setTimeout(() => {
                insertingMention = false;
            }, 100);
        }

        function getCursorPosition(element) {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                if (element.contains(range.startContainer)) {
                    return range.startOffset;
                }
            }
            return element.textContent.length;
        }

        function setCursorPosition(element, position) {
            const range = document.createRange();
            const selection = window.getSelection();
            
            if (element.firstChild) {
                range.setStart(element.firstChild, Math.min(position, element.firstChild.length));
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        }

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
    </script>
</div>
