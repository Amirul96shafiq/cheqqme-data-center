<div class="flex flex-col flex-1 h-full min-h-0 rounded-xl bg-white dark:bg-gray-900">

    <!-- Composer (Top) -->
    <div class="px-4 pt-4 pb-3 bg-white dark:bg-gray-900" data-composer>
        <div class="space-y-2">
            <textarea wire:model.defer="newComment" rows="3" placeholder="Write a comment..." class="w-full text-sm leading-snug rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring focus:ring-primary-500/20 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 p-3 resize-y placeholder:text-gray-400 dark:placeholder:text-gray-500"></textarea>
            @error('newComment') <p class="text-xs text-danger-600">{{ $message }}</p> @enderror
            <button wire:click="addComment" wire:loading.attr="disabled" type="button" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500/50 disabled:opacity-50">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                <span wire:loading.remove>Send</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </div>

    <!-- Comments List (scroll area) -->
    <div class="flex-1 min-h-0 px-0 pb-0">
        <div class="px-4 py-4 space-y-4 text-sm overflow-y-auto custom-thin-scroll h-full" data-comment-list style="max-height:calc(68vh - 250px);">
            @forelse($this->comments as $comment)
                <div class="group relative flex gap-3" wire:key="comment-{{ $comment->id }}">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white flex items-center justify-center text-[11px] font-medium shadow-sm">
                            {{ mb_strtoupper(mb_substr($comment->user->username ?? 'U',0,1)) }}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex flex-col">
                                    <span class="comment-username text-gray-900 dark:text-gray-100 leading-none">{{ $comment->user->username ?? 'Unknown' }}</span>
                                    <span class="mt-1 comment-meta text-gray-500 dark:text-gray-400" title="{{ $comment->created_at->format('Y-m-d H:i') }}">
                                        {{ $comment->created_at->diffForHumans(short: true) }} · {{ $comment->created_at->format('Y-m-d H:i') }}
                                    @if($comment->updated_at->gt($comment->created_at))
                                            <span class="italic text-gray-400 comment-meta">· edited</span>
                                    @endif
                                </span>
                            </div>
                            @if(auth()->id() === $comment->user_id)
                                <div class="flex items-center gap-1">
                                    @if($this->editingId !== $comment->id)
                                        <button type="button" wire:click="startEdit({{ $comment->id }})" class="p-1.5 rounded-md text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/40" title="Edit">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button type="button" wire:click="confirmDelete({{ $comment->id }})" class="p-1.5 rounded-md text-gray-400 focus:outline-none focus:ring-2 focus:ring-danger-500/40" title="Delete">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="mt-2">
                            @if($this->editingId === $comment->id)
                                <div class="space-y-2">
                                    <textarea wire:model.defer="editingText" rows="3" class="w-full text-sm leading-snug rounded-lg border border-primary-300 dark:border-primary-500 focus:border-primary-500 focus:ring focus:ring-primary-500/20 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 p-2 resize-y"></textarea>
                                    <div class="flex items-center gap-2">
                                        <button wire:click="saveEdit" type="button" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/50">Save</button>
                                        <button wire:click="cancelEdit" type="button" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200">Cancel</button>
                                    </div>
                                </div>
                            @else
                                <div class="prose prose-xs dark:prose-invert max-w-none">
                                    <p class="m-0 leading-snug text-[13px] text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">{{ $comment->comment }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-2 py-8 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No comments yet.</p>
                </div>
            @endforelse
    <div class="mt-2 text-[10px] text-gray-400">Showing {{ $this->comments->count() }} of {{ $this->totalComments }} comments</div>
    @if($this->totalComments > $visibleCount)
            @php $remaining = $this->totalComments - $visibleCount; @endphp
            <div class="pt-2">
                <button wire:click="showMore" type="button" class="w-full text-xs font-medium px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500/40">Show more ({{ $remaining < 5 ? $remaining : 5 }})</button>
            </div>
        @endif
        </div>
    </div>
    @if($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75" wire:click="cancelDelete"></div>
            <div class="fi-modal-window relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6">
                <button type="button" wire:click="cancelDelete" class="fi-modal-close-btn absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <div class="flex flex-col items-center text-center">
                    <div class="mb-5 flex items-center justify-center">
                        <div class="p-3 rounded-full bg-danger-100 text-danger-600 dark:bg-danger-500/20 dark:text-danger-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </div>
                    </div>
                    <h2 class="fi-modal-heading text-sm font-medium text-gray-900 dark:text-gray-100">Delete Comment</h2>
                    <p class="fi-modal-description mt-2 text-xs leading-relaxed text-gray-600 dark:text-gray-400">Are you sure you would like to delete this comment? This action can be reversed only by an admin.</p>
                    <div class="mt-6 flex w-full items-stretch gap-3">
                        <button type="button" wire:click="cancelDelete" class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-xs font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">Cancel</button>
                        <button type="button" wire:click="performDelete" class="fi-btn fi-color-danger flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-xs font-medium tracking-tight bg-danger-600 text-white hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-danger-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-danger-600 dark:hover:bg-danger-500 dark:focus:ring-offset-gray-900">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @once
        <style>
            .custom-thin-scroll::-webkit-scrollbar { width: 6px; }
            .custom-thin-scroll::-webkit-scrollbar-track { background: transparent; }
            .custom-thin-scroll::-webkit-scrollbar-thumb { background: rgba(100,116,139,.35); border-radius: 3px; }
            .dark .custom-thin-scroll::-webkit-scrollbar-thumb { background: rgba(148,163,184,.30); }
            .custom-thin-scroll:hover::-webkit-scrollbar-thumb { background: rgba(100,116,139,.55); }
            .dark .custom-thin-scroll:hover::-webkit-scrollbar-thumb { background: rgba(148,163,184,.50); }
            .custom-thin-scroll { scrollbar-width: thin; scrollbar-color: rgba(148,163,184,.35) transparent; }
            .dark .custom-thin-scroll { scrollbar-color: rgba(148,163,184,.35) transparent; }
            .comment-username { font-size: 14px; font-weight: 700 ;}
            .comment-meta { font-size: 11px; line-height: 1rem; }

            /* (Removed fallback danger utilities – native Tailwind classes now present) */
        </style>
    @endonce
</div>
