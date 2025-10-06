<!-- Task Comments Component -->
<div class="flex flex-col flex-1 h-full min-h-0 rounded-xl" 
    x-data="{
         ...notificationHandler(),
         isFocusMode: false,
         focusedCommentId: null,
        baseEditPath: null,
        lastSharedId: null,
        feedbackTimeoutId: null,
        // Livewire-entangled composer value for enabling/disabling submit button
        composerValue: $wire.entangle('composerData.newComment').live,
        async shareComment(commentId) {
            try {
                const base = this.baseEditPath ?? (window.location.pathname || '').replace(/\/(comments)\/(\d+)(?:\/)?$/, '');
                const path = `${base.replace(/\/(comments)\/(\d+)(?:\/)?$/, '')}/comments/${commentId}`;
                const url = `${window.location.origin}${path}`;
                await navigator.clipboard.writeText(url);
                // Filament native notification
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { status: 'success', message: 'Comment link copied' }
                }));
                // Inline feedback near the clicked button (2s)
                this.lastSharedId = Number(commentId);
                if (this.feedbackTimeoutId) clearTimeout(this.feedbackTimeoutId);
                this.feedbackTimeoutId = setTimeout(() => { this.lastSharedId = null; }, 2000);
            } catch (e) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { status: 'danger', message: 'Failed to copy link' }
                }));
                this.lastSharedId = Number(commentId);
                if (this.feedbackTimeoutId) clearTimeout(this.feedbackTimeoutId);
                this.feedbackTimeoutId = setTimeout(() => { this.lastSharedId = null; }, 2000);
            }
        },
        init() {
            // Determine base edit path (strip any /comments/{id}) and sync UI from URL
            const path = window.location.pathname || '';
            const match = path.match(/\/(comments)\/(\d+)(?:\/)?$/);
            if (match) {
                this.baseEditPath = path.replace(/\/(comments)\/(\d+)(?:\/)?$/, '');
                const id = Number(match[2]);
                if (!Number.isNaN(id)) {
                    this.focusedCommentId = id;
                    this.isFocusMode = true;
                }
            } else {
                this.baseEditPath = path;
                // Also support query param focus_comment for deep links that were redirected
                const params = new URLSearchParams(window.location.search);
                const q = params.get('focus_comment');
                if (q) {
                    const qid = Number(q);
                    if (!Number.isNaN(qid)) {
                        this.focusedCommentId = qid;
                        this.isFocusMode = true;
                        // Normalize URL to /comments/{id} without reloading
                        try {
                            const newPath = `${this.baseEditPath}/comments/${qid}`;
                            history.replaceState({ commentId: qid }, '', newPath);
                        } catch (e) {}
                    }
                }
            }

            // Keep UI in sync with browser navigation (back/forward)
            window.addEventListener('popstate', () => {
                const p = window.location.pathname || '';
                const m = p.match(/\/(comments)\/(\d+)(?:\/)?$/);
                if (m) {
                    const cid = Number(m[2]);
                    if (!Number.isNaN(cid)) {
                        this.focusedCommentId = cid;
                        this.isFocusMode = true;
                    }
                } else {
                    this.isFocusMode = false;
                    this.focusedCommentId = null;
                }
            });
        },
         enterFocusMode(commentId) {
             this.focusedCommentId = commentId;
             this.isFocusMode = true;
            // Push focus URL without reloading
            try {
                const base = this.baseEditPath ?? (window.location.pathname || '');
                const newPath = `${base.replace(/\/(comments)\/(\d+)(?:\/)?$/, '')}/comments/${commentId}`;
                if (window.location.pathname !== newPath) {
                    history.pushState({ commentId }, '', newPath);
                }
            } catch (e) {
                // no-op
            }
         },
         exitFocusMode() {
             this.isFocusMode = false;
             this.focusedCommentId = null;
            // Restore base edit URL without reloading
            try {
                const base = this.baseEditPath ?? (window.location.pathname || '').replace(/\/(comments)\/(\d+)(?:\/)?$/, '');
                if (window.location.pathname !== base) {
                    history.pushState({}, '', base);
                }
            } catch (e) {
                // no-op
            }
         }
     }"
    x-init="init()"
     x-on:keydown.ctrl.enter.prevent="
         ($event.target.closest('[data-composer]') || $event.target.closest('.minimal-comment-editor')) && 
         ($wire.editingId === null || $wire.editingId === undefined) && 
         composerValue && composerValue.replace(/<[^>]+>/g, '').trim() !== '' &&
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
    <div class="px-0 pt-0 pb-5" data-composer x-show="!isFocusMode">
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
            <button 
                wire:click="addComment" 
                wire:loading.attr="disabled" 
                wire:target="addComment" 
                type="button" 
                :disabled="!composerValue || composerValue.replace(/<[^>]+>/g, '').trim() === ''"
                :aria-disabled="!composerValue || composerValue.replace(/<[^>]+>/g, '').trim() === ''"
                class="w-full inline-flex items-center justify-center gap-1.5 p-2.5 bg-primary-600 hover:bg-primary-500 disabled:hover:bg-primary-600 text-primary-900 text-sm font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500/50 disabled:opacity-50 disabled:cursor-not-allowed relative">
                <div class="flex items-center gap-1.5">
                    <span wire:loading.remove wire:target="addComment">{{ __('comments.composer.send') }}</span>
                    <span wire:loading wire:target="addComment">{{ __('comments.composer.saving') }}</span>
                </div>
                <div class="absolute right-2 flex items-center gap-1 text-primary-800 text-[10px] font-semibold">
                    <kbd class="px-1 py-0.5 bg-primary-transparent border border-primary-800 rounded font-mono">CTRL + ENTER</kbd>
                </div>
            </button>

        </div>
    </div>

    <!-- Comments List (scroll area) -->
    <div class="flex-1 min-h-0 px-0 pb-0" :class="isFocusMode ? 'focus-mode-parent' : ''">
        <div class="px-2 pb-6 text-sm overflow-y-auto custom-thin-scroll h-full comment-list-container flex flex-col" 
             data-comment-list
             :class="isFocusMode ? 'focus-mode-full-height' : ''">

            <!-- Focus Mode Exit Button - Sticky at Top -->
            <div wire:ignore
                 x-show="isFocusMode" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform translate-y-2"
                 class="sticky top-0 z-20 bg-white dark:bg-gray-900 pb-2 border-b border-gray-200 dark:border-gray-700 mb-4">
                <button x-on:click="exitFocusMode()" 
                        type="button" 
                        class="w-full text-xs font-medium px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                    {{ __('comments.buttons.exit_focus_mode') }}
                </button>
            </div>
            
            <div class="space-y-6 flex-1 pt-1">
                <!-- Loop through comments -->
                @forelse($this->comments as $comment)
                    <div class="group relative flex gap-3" 
                         wire:key="comment-{{ $comment->id }}" 
                         data-comment-id="{{ $comment->id }}"
                         x-show="!isFocusMode || focusedCommentId === {{ $comment->id }}"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95">
                        <div class="flex-shrink-0 relative">
                            <x-clickable-avatar-wrapper :user="$comment->user">
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
                                
                                <!-- Online Status Indicator -->
                                <div class="relative bottom-3 -right-7 z-20">
                                    <x-online-status-indicator :user="$comment->user" size="md" />
                                </div>
                            </x-clickable-avatar-wrapper>
                        <!-- Vertical connecting line that extends from avatar -->
                        <div class="absolute left-1/2 top-10 w-[0.5px] {{ auth()->id() === $comment->user_id ? 'bg-primary-500/80' : 'bg-gray-300/80 dark:bg-gray-600/80' }} {{ $comment->isDeleted() ? 'opacity-25' : '' }} transform -translate-x-1/2 z-0" style="height: calc(100% + 1.5rem);"></div>
                        </div>
                        <!-- Comment content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-col">
                                        <span class="comment-username text-gray-900 dark:text-gray-100 leading-none">{{ $comment->user->username ?? __('comments.meta.unknown') }}</span>
                                        <span class="mt-1 comment-meta text-gray-500 dark:text-gray-400" title="{{ $comment->isDeleted() && $comment->deletion_timestamp ? $comment->deletion_timestamp->format('j/n/y, h:i A') : $comment->created_at->format('j/n/y, h:i A') }}">
                                            @if($comment->isDeleted() && $comment->deletion_timestamp)
                                                {{ $comment->deletion_timestamp->diffForHumans(short: true) }} · {{ $comment->deletion_timestamp->format('j/n/y, h:i A') }}
                                                <span class="italic text-gray-400 comment-meta">· {{ __('comments.meta.deleted') }}</span>
                                            @else
                                                {{ $comment->created_at->diffForHumans(short: true) }} · {{ $comment->created_at->format('j/n/y, h:i A') }}
                                                @if($comment->updated_at->gt($comment->created_at))
                                                    <span class="italic text-gray-400 comment-meta">· {{ __('comments.meta.edited') }}</span>
                                                @endif
                                            @endif
                                        </span>
                                </div>
                                
                                <!-- Action buttons: Reply (separate) + Group actions dropdown -->
                                @if($this->editingId !== $comment->id && $this->replyingToId !== $comment->id)
                                    <div class="flex items-center gap-1">
                                        @if(!$comment->isDeleted())

                                            <!-- Reply button (always visible, separate from group actions) -->
                                            <button type="button" 
                                                    wire:click="startReply({{ $comment->id }})" 
                                                    class="flex items-center justify-center gap-1 px-2 py-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-900/20 focus:outline-none focus:ring-2 focus:ring-primary-500/40 transition-all duration-200" 
                                                    title="{{ __('comments.buttons.reply') }}"
                                                    wire:loading.attr="disabled"
                                                    wire:target="startReply({{ $comment->id }})">
                                                
                                                    <!-- Loading spinner -->
                                                <svg wire:loading wire:target="startReply({{ $comment->id }})" 
                                                     class="animate-spin w-3 h-3 text-gray-400" 
                                                     xmlns="http://www.w3.org/2000/svg" 
                                                     fill="none" 
                                                     viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>

                                                <!-- Button text -->
                                                <span class="text-[10px] font-light" 
                                                      wire:loading.remove 
                                                      wire:target="startReply({{ $comment->id }})">{{ __('comments.buttons.reply') }}
                                                </span>
                                                
                                            </button>

                                            <!-- Share button (copy focus link) -->
                                            <div class="flex items-center">
                                                <button type="button"
                                                        x-on:click="shareComment({{ $comment->id }})"
                                                        class="flex items-center justify-center gap-1 px-2 py-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-900/20 focus:outline-none focus:ring-2 focus:ring-primary-500/40 transition-all duration-200"
                                                        title="{{ __('Share') }}">
                                                    @svg('heroicon-o-share', 'w-3 h-3')
                                                </button>
                                                <span class="ml-1 text-[10px] text-primary-600 dark:text-primary-400" x-show="lastSharedId === {{ $comment->id }}" x-transition.opacity.duration.150ms>{{ __('Copied') }}</span>
                                            </div>
                                            
                                            <!-- Group actions dropdown (Focus, Edit, Delete) -->
                                            @if(auth()->id() === $comment->user_id)
                                                <x-comment-actions-dropdown 
                                                    :comment-id="$comment->id"
                                                    :is-reply="false"
                                                    :can-edit="true"
                                                    :can-delete="true"
                                                    :can-force-delete="false"
                                                    :show-reply="false"
                                                    :show-focus="true"
                                                />
                                            @else
                                                <x-comment-actions-dropdown 
                                                    :comment-id="$comment->id"
                                                    :is-reply="false"
                                                    :can-edit="false"
                                                    :can-delete="false"
                                                    :can-force-delete="false"
                                                    :show-reply="false"
                                                    :show-focus="true"
                                                />
                                            @endif
                                        @else
                                            <!-- Force delete dropdown for deleted comments -->
                                            @if(auth()->id() === $comment->user_id)
                                                <x-comment-actions-dropdown 
                                                    :comment-id="$comment->id"
                                                    :is-reply="false"
                                                    :can-edit="false"
                                                    :can-delete="false"
                                                    :can-force-delete="true"
                                                    :show-reply="false"
                                                    :show-focus="false"
                                                />
                                            @endif
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
                                                    {{ __('comments.buttons.save_edit') }}
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
                                    <div class="bg-gray-300/15 dark:bg-gray-800/50 rounded-lg p-3 mt-4 transition-colors duration-200 relative {{ $comment->isDeleted() ? 'opacity-25' : '' }}">
                                        <div class="prose prose-xs dark:prose-invert max-w-none leading-snug text-[13px] text-gray-700 dark:text-gray-300 break-words">{!! $comment->rendered_comment !!}</div>
                                    </div>
                                    
                                    <!-- Comment Reactions -->
                                    @if(!$comment->isDeleted())
                                        <x-comment-reactions :comment="$comment" />
                                    @endif
                                    
                                    <!-- Reply form -->
                                    @if($this->replyingToId === $comment->id)
                                        <div class="mt-4 space-y-2">
                                            <div class="fi-form reply-form" data-reply-form="true">{{ $this->replyForm }}</div>
                                            @error('replyText') 
                                                <p class="text-xs text-danger-600" 
                                                   wire:key="error-replyText-{{ $comment->id }}-{{ time() }}"
                                                   x-data="{ show: true }" 
                                                   x-show="show" 
                                                   x-init="
                                                       setTimeout(() => show = false, 3000);
                                                       $wire.on('reply-added', () => show = false);
                                                   "
                                                   x-transition:leave="transition ease-in duration-300"
                                                   x-transition:leave-start="opacity-100"
                                                   x-transition:leave-end="opacity-0">
                                                   {{ $message }}
                                                </p> 
                                            @enderror
                                            <div class="flex items-center gap-2">
                                                <!-- Submit Reply button -->
                                                <button wire:click="addReply" 
                                                        type="button" 
                                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-primary-600 text-primary-900 hover:bg-primary-500 hover:dark:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                                                        wire:loading.attr="disabled"
                                                        wire:target="addReply">
                                                    <span wire:loading.remove wire:target="addReply">
                                                        {{ __('comments.buttons.send_reply') }}
                                                    </span>
                                                    <span wire:loading wire:target="addReply">
                                                        {{ __('comments.buttons.submitting') }}
                                                    </span>
                                                </button>
                                                
                                                <!-- Cancel Reply button -->
                                                <button wire:click="cancelReply" 
                                                        type="button" 
                                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                                                        wire:loading.attr="disabled"
                                                        wire:target="cancelReply">

                                                    <!-- Loading spinner -->
                                                    <svg wire:loading wire:target="cancelReply" 
                                                         class="animate-spin w-3 h-3 text-gray-700 dark:text-gray-300 mr-1" 
                                                         xmlns="http://www.w3.org/2000/svg" 
                                                         fill="none" 
                                                         viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    
                                                    <!-- Button text -->
                                                    <span wire:loading.remove wire:target="cancelReply">
                                                        {{ __('comments.buttons.cancel') }}
                                                    </span>

                                                </button>

                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Display replies -->
                                    @if($comment->replies->count() > 0)
                                        <!-- Show/Hide replies button -->
                                        <div class="mt-3" x-data="{ 
                                            expanded: @js(in_array($comment->id, $expandedReplies)),
                                            toggle() { 
                                                this.expanded = !this.expanded;
                                            }
                                        }" 
                                        x-init="
                                            $watch('$wire.expandedReplies', (value) => {
                                                expanded = value.includes({{ $comment->id }});
                                            });
                                        ">
                                            <button type="button" 
                                                    @click="toggle()" 
                                                    class="text-[10px] text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors duration-200 flex items-center gap-1">
                                                <span x-show="expanded" class="flex items-center gap-1">
                                                    @svg('heroicon-o-chevron-up', 'w-3 h-3')
                                                    {{ __('comments.buttons.hide_replies') }}
                                                </span>
                                                <span x-show="!expanded" class="flex items-center gap-1">
                                                    @svg('heroicon-o-chevron-down', 'w-3 h-3')
                                                    {{ __('comments.buttons.show_replies', ['count' => $comment->replies->count()]) }}
                                                </span>
                                            </button>
                                            
                                            <!-- Replies container -->
                                            <div x-show="expanded" 
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 transform scale-95"
                                                 x-transition:enter-end="opacity-100 transform scale-100"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 transform scale-100"
                                                 x-transition:leave-end="opacity-0 transform scale-95"
                                                 class="mt-4 space-y-3">
                                                @foreach($comment->replies as $reply)
                                                <div class="group flex gap-3" wire:key="reply-{{ $reply->id }}" data-comment-id="{{ $reply->id }}">
                                                    <div class="flex-shrink-0 relative">
                                                        <!-- Horizontal connecting line for first reply -->
                                                        @if($loop->first)
                                                            <div class="absolute -left-8 top-4 w-10 h-[0.5px] {{ auth()->id() === $comment->user_id ? 'bg-primary-500/80' : 'bg-gray-300/80 dark:bg-gray-600/80' }} {{ $reply->isDeleted() ? 'opacity-25' : '' }} z-0"></div>
                                                        @endif
                                                        @php
                                                            $avatarPath = $reply->user->avatar ?? null;
                                                            $avatarUrl = $avatarPath ? \Storage::url($avatarPath) : null;
                                                        @endphp
                                                        @if($avatarUrl)
                                                            <div class="relative">
                                                                <x-clickable-avatar-wrapper :user="$reply->user">
                                                                    <img src="{{ $avatarUrl }}" alt="{{ $reply->user->username ?? __('comments.meta.user_fallback') }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-white/20 dark:ring-gray-800 shadow-sm relative z-10 {{ auth()->id() === $reply->user_id ? 'border-2 border-primary-500/80' : '' }}" loading="lazy">
                                                                </x-clickable-avatar-wrapper>
                                                                <!-- Online Status Indicator for Reply Avatar (User Image) -->
                                                                <div class="absolute -bottom-0.5 -right-0.5 z-20">
                                                                    <x-online-status-indicator :user="$reply->user" size="sm" />
                                                                </div>
                                                            </div>
                                                        @else
                                                            @php
                                                                $defaultAvatarUrl = (new \Filament\AvatarProviders\UiAvatarsProvider())->get($reply->user);
                                                            @endphp
                                                            @if($defaultAvatarUrl)
                                                                <div class="relative">
                                                                    <x-clickable-avatar-wrapper :user="$reply->user">
                                                                        <img src="{{ $defaultAvatarUrl }}" alt="{{ $reply->user->username ?? __('comments.meta.user_fallback') }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-white/20 dark:ring-gray-800 shadow-sm relative z-10 {{ auth()->id() === $reply->user_id ? 'border-2 border-primary-500/80' : '' }}" loading="lazy">
                                                                    </x-clickable-avatar-wrapper>
                                                                    <!-- Online Status Indicator for Reply Avatar (Default Avatar Image) -->
                                                                    <div class="absolute -bottom-0.5 -right-0.5 z-20">
                                                                        <x-online-status-indicator :user="$reply->user" size="sm" />
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="relative">
                                                                    <x-clickable-avatar-wrapper :user="$reply->user">
                                                                        <div class="w-8 h-8 rounded-full bg-primary-500 ring-1 ring-white/20 dark:ring-gray-800 shadow-sm flex items-center justify-center relative z-10 {{ auth()->id() === $reply->user_id ? 'border-2 border-white/80' : '' }}">
                                                                            <span class="text-xs font-medium text-white">
                                                                                {{ substr($reply->user->username ?? __('comments.meta.user_fallback'), 0, 1) }}
                                                                            </span>
                                                                        </div>
                                                                    </x-clickable-avatar-wrapper>
                                                                    <!-- Online Status Indicator for Reply Avatar (Initial Avatar) -->
                                                                    <div class="absolute -bottom-0.5 -right-0.5 z-20">
                                                                        <x-online-status-indicator :user="$reply->user" size="sm" />
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                        
                                                        <!-- Vertical connecting line below reply avatar -->
                                                        @if(!$loop->last)
                                                            <div class="absolute left-1/2 top-8 w-[0.5px] {{ auth()->id() === $comment->user_id ? 'bg-primary-500/80' : 'bg-gray-300/80 dark:bg-gray-600/80' }} {{ $reply->isDeleted() ? 'opacity-25' : '' }} transform -translate-x-1/2 z-0" style="height: calc(100% + 0.75rem);"></div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div class="flex flex-col">
                                                                <span class="comment-username text-gray-900 dark:text-gray-100 leading-none text-sm">{{ $reply->user->username ?? __('comments.meta.unknown') }}</span>
                                                                <span class="mt-1 comment-meta text-gray-500 dark:text-gray-400 text-xs" title="{{ $reply->isDeleted() && $reply->deletion_timestamp ? $reply->deletion_timestamp->format('j/n/y, h:i A') : $reply->created_at->format('j/n/y, h:i A') }}">
                                                                    @if($reply->isDeleted() && $reply->deletion_timestamp)
                                                                        {{ $reply->deletion_timestamp->diffForHumans(short: true) }} · {{ $reply->deletion_timestamp->format('j/n/y, h:i A') }}
                                                                        <span class="italic text-gray-400 comment-meta">· {{ __('comments.meta.deleted') }}</span>
                                                                    @else
                                                                        {{ $reply->created_at->diffForHumans(short: true) }} · {{ $reply->created_at->format('j/n/y, h:i A') }}
                                                                        @if($reply->updated_at->gt($reply->created_at))
                                                                            <span class="italic text-gray-400 comment-meta">· {{ __('comments.meta.edited') }}</span>
                                                                        @endif
                                                                    @endif
                                                                </span>
                                                            </div>
                                                             <!-- Reply group actions dropdown (Focus, Edit, Delete) -->
                                                             @if($this->editingReplyId !== $reply->id)
                                                                 <div class="flex items-center gap-1">
                                                                     @if(!$reply->isDeleted())
                                                                         @if(auth()->id() === $reply->user_id)
                                                                             <x-comment-actions-dropdown 
                                                                                 :comment-id="$reply->id"
                                                                                 :is-reply="true"
                                                                                 :can-edit="true"
                                                                                 :can-delete="true"
                                                                                 :can-force-delete="false"
                                                                                 :show-reply="false"
                                                                                 :show-focus="true"
                                                                             />
                                                                         @else
                                                                             <x-comment-actions-dropdown 
                                                                                 :comment-id="$reply->id"
                                                                                 :is-reply="true"
                                                                                 :can-edit="false"
                                                                                 :can-delete="false"
                                                                                 :can-force-delete="false"
                                                                                 :show-reply="false"
                                                                                 :show-focus="true"
                                                                             />
                                                                         @endif
                                                                     @else
                                                                         <!-- Force delete dropdown for deleted replies -->
                                                                         @if(auth()->id() === $reply->user_id)
                                                                             <x-comment-actions-dropdown 
                                                                                 :comment-id="$reply->id"
                                                                                 :is-reply="true"
                                                                                 :can-edit="false"
                                                                                 :can-delete="false"
                                                                                 :can-force-delete="true"
                                                                                 :show-reply="false"
                                                                                 :show-focus="false"
                                                                             />
                                                                         @endif
                                                                     @endif
                                                                 </div>
                                                             @endif
                                                        </div>
                                                        <div class="mt-2">
                                                            <!-- Edit reply form -->
                                                            @if($this->editingReplyId === $reply->id)
                                                                <div class="space-y-2">
                                                                    <div class="fi-form edit-reply-form" data-edit-reply-form="true">{{ $this->editReplyForm }}</div>
                                                                    <div class="flex items-center gap-2">
                                                                        <!-- Save Edit Reply button -->
                                                                        <button wire:click="saveEditReply" 
                                                                                type="button" 
                                                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-primary-600 text-primary-900 hover:bg-primary-500 hover:dark:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                                                                                wire:loading.attr="disabled"
                                                                                wire:target="saveEditReply">
                                                                            <span wire:loading.remove wire:target="saveEditReply">
                                                                                {{ __('comments.buttons.save_edit') }}
                                                                            </span>
                                                                            <span wire:loading wire:target="saveEditReply">
                                                                                {{ __('comments.buttons.submitting') }}
                                                                            </span>
                                                                        </button>
                                                                        <!-- Cancel Edit Reply button -->
                                                                        <button wire:click="cancelEditReply" 
                                                                                type="button" 
                                                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500/50"
                                                                                wire:loading.attr="disabled"
                                                                                wire:target="cancelEditReply">
                                                                            {{ __('comments.buttons.cancel') }}
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="bg-gray-200/20 dark:bg-gray-700/30 rounded-lg p-2 mt-2 relative {{ $reply->isDeleted() ? 'opacity-25' : '' }}">
                                                                    <div class="prose prose-xs dark:prose-invert max-w-none leading-snug text-[12px] text-gray-700 dark:text-gray-300 break-words">{!! $reply->rendered_comment !!}</div>
                                                                </div>
                                                                <!-- Reply Reactions -->
                                                                @if(!$reply->isDeleted())
                                                                    <x-comment-reactions :comment="$reply" />
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
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
                <div class="mt-3 text-[10px] text-gray-400 text-center relative z-10" x-show="!isFocusMode">{{ __('comments.list.showing', ['shown' => $this->comments->count(), 'total' => $this->totalComments]) }}</div>
            @endif
            <!-- Show more comments button -->
            @if($this->totalComments > $visibleCount)
                @php $remaining = $this->totalComments - $visibleCount; @endphp
                <div class="mt-2 relative z-10" x-show="!isFocusMode">
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
        
        /* Full height when in focus mode */
        .comment-list-container.focus-mode-full-height {
            min-height: calc(80vh - 120px) !important;
            max-height: calc(80vh - 120px) !important;
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

    <!-- User Mention System is loaded via app.js -->

    <!-- Livewire Comments Emoji Picker Functions -->
    <script>
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

    {{-- Dropdown component moved to global layout to prevent Livewire re-rendering issues --}}
    
    <!-- Floating Emoji Picker Container for Livewire Comments -->
    <div id="livewire-comments-emoji-picker-container" class="fixed hidden z-[11]">
        <emoji-picker id="livewire-comments-emoji-picker"></emoji-picker>
    </div>
    
    <!-- Emoji Picker Element -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    
    <!-- Task Comments CSS -->
    @vite('resources/css/task-comments.css')
    
    <!-- Emoji Picker Theme CSS -->
    @vite('resources/css/emoji-picker-theme.css')
</div>
