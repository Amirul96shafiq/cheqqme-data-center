@props([])

<!-- Global Modal Container for Global Modals -->
<div id="global-modal-container" 
     x-data="globalModalContainer()"
     class="fixed inset-0 z-[99999] pointer-events-none"
     style="z-index: 99999 !important;">
    
    <!-- Delete Comment Modal -->
    <div x-show="modals.deleteComment.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">
        
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75" 
             @click="closeModal('deleteComment')" 
             aria-hidden="true"></div>
        
        <!-- Modal -->
        <div role="dialog" 
             aria-modal="true" 
             aria-labelledby="delete-comment-heading" 
             class="relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">
            
            <!-- Close Button -->
            <button type="button" 
                    @click="closeModal('deleteComment')" 
                    class="absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900" 
                    aria-label="Close">
                <svg class="w-6 h-6" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            
            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-5 flex items-center justify-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400">
                        <x-heroicon-o-trash class="h-6 w-6" />
                    </div>
                </div>
                
                <h2 id="delete-comment-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('comments.modal.delete.title') }}
                </h2>
                
                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('comments.modal.delete.description') }}
                </p>
                
                <!-- Actions -->
                <div class="mt-6 flex w-full items-stretch gap-3">
                    <button type="button" 
                            @click="closeModal('deleteComment')" 
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">
                        {{ __('comments.modal.delete.cancel') }}
                    </button>
                    
                    <button type="button" 
                            @click="confirmDelete()" 
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-yellow-600 text-white hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-yellow-600 dark:hover:bg-yellow-500 dark:focus:ring-offset-gray-900">
                        {{ __('comments.modal.delete.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Reply Modal -->
    <div x-show="modals.deleteReply.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">
        
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75" 
             @click="closeModal('deleteReply')" 
             aria-hidden="true"></div>
        
        <!-- Modal -->
        <div role="dialog" 
             aria-modal="true" 
             aria-labelledby="delete-reply-heading" 
             class="relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">
            
            <!-- Close Button -->
            <button type="button" 
                    @click="closeModal('deleteReply')" 
                    class="absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900" 
                    aria-label="Close">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            
            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-5 flex items-center justify-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400">
                        <x-heroicon-o-trash class="h-6 w-6" />
                    </div>
                </div>
                
                <h2 id="delete-reply-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('comments.modal.delete_reply.title') }}
                </h2>
                
                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('comments.modal.delete_reply.description') }}
                </p>
                
                <!-- Actions -->
                <div class="mt-6 flex w-full items-stretch gap-3">
                    <button type="button" 
                            @click="closeModal('deleteReply')" 
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">
                        {{ __('comments.modal.delete.cancel') }}
                    </button>
                    
                    <button type="button" 
                            @click="confirmDeleteReply()" 
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-yellow-600 text-white hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-yellow-600 dark:hover:bg-yellow-500 dark:focus:ring-offset-gray-900">
                        {{ __('comments.modal.delete.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Force Delete Comment Modal -->
    <div x-show="modals.forceDeleteComment.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">
        
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75" 
             @click="closeModal('forceDeleteComment')" 
             aria-hidden="true"></div>
        
        <!-- Modal -->
        <div role="dialog" 
             aria-modal="true" 
             aria-labelledby="force-delete-comment-heading" 
             class="relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">
            
            <!-- Close Button -->
            <button type="button" 
                    @click="closeModal('forceDeleteComment')" 
                    class="absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900" 
                    aria-label="Close">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            
            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-5 flex items-center justify-center">
                    <div class="p-3 rounded-full bg-danger-100 text-danger-600 dark:bg-danger-500/20 dark:text-danger-400">
                        <x-heroicon-o-trash class="h-6 w-6" />
                    </div>
                </div>
                
                <h2 id="force-delete-comment-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('comments.modal.force_delete.title') }}
                </h2>
                
                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('comments.modal.force_delete.description') }}
                </p>
                
                <!-- Actions -->
                <div class="mt-6 flex w-full items-stretch gap-3">
                    <button type="button" 
                            @click="closeModal('forceDeleteComment')" 
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">
                        {{ __('comments.modal.force_delete.cancel') }}
                    </button>
                    
                    <button type="button" 
                            @click="confirmForceDelete()" 
                            class="fi-btn fi-color-danger flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-danger-600 text-white hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-danger-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-danger-600 dark:hover:bg-danger-500 dark:focus:ring-offset-gray-900">
                        {{ __('comments.modal.force_delete.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Force Delete Reply Modal -->
    <div x-show="modals.forceDeleteReply.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75"
             @click="closeModal('forceDeleteReply')"
             aria-hidden="true"></div>

        <!-- Modal -->
        <div role="dialog"
             aria-modal="true"
             aria-labelledby="force-delete-reply-heading"
             class="relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">

            <!-- Close Button -->
            <button type="button"
                    @click="closeModal('forceDeleteReply')"
                    class="absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                    aria-label="Close">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>

            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-5 flex items-center justify-center">
                    <div class="p-3 rounded-full bg-danger-100 text-danger-600 dark:bg-danger-500/20 dark:text-danger-400">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                            <path d="M10 11v6"/>
                            <path d="M14 11v6"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                    </div>
                </div>

                <h2 id="force-delete-reply-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('comments.modal.force_delete_reply.title') }}
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('comments.modal.force_delete_reply.description') }}
                </p>

                <!-- Actions -->
                <div class="mt-6 flex w-full items-stretch gap-3">
                    <button type="button"
                            @click="closeModal('forceDeleteReply')"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">
                        {{ __('comments.modal.force_delete.cancel') }}
                    </button>

                    <button type="button"
                            @click="confirmForceDeleteReply()"
                            class="fi-btn fi-color-danger flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-danger-600 text-white hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-danger-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-danger-600 dark:hover:bg-danger-500 dark:focus:ring-offset-gray-900">
                        {{ __('comments.modal.force_delete.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Backup Modal -->
    <div x-show="modals.createBackup.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75"
             @click="closeModal('createBackup')"
             aria-hidden="true"></div>

        <!-- Modal -->
        <div role="dialog"
             aria-modal="true"
             aria-labelledby="create-backup-heading"
             class="relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">

            <!-- Close Button -->
            <button type="button"
                    @click="closeModal('createBackup')"
                    class="absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                    aria-label="Close">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>

            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-5 flex items-center justify-center">
                    <div class="p-3 rounded-full bg-primary-100 text-primary-600 dark:bg-primary-500/20 dark:text-primary-400">
                        <x-heroicon-o-archive-box class="h-6 w-6" />
                    </div>
                </div>

                <h2 id="create-backup-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('settings.chatbot.confirm_backup_creation') }}
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('settings.chatbot.confirm_backup_description') }}
                </p>

                <!-- Actions -->
                <div class="mt-6 flex w-full items-stretch gap-3">
                    <button type="button"
                            @click="closeModal('createBackup')"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">
                        {{ __('Cancel') }}
                    </button>

                    <button type="button"
                            @click="confirmCreateBackup()"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-primary-600 text-primary-900 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-primary-600 dark:hover:bg-primary-500 dark:focus:ring-offset-gray-900">
                        {{ __('settings.chatbot.create_backup') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Backup Modal -->
    <div x-show="modals.restoreBackup.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75"
             @click="closeModal('restoreBackup')"
             aria-hidden="true"></div>

        <!-- Modal -->
        <div role="dialog"
             aria-modal="true"
             aria-labelledby="restore-backup-heading"
             class="relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">

            <!-- Close Button -->
            <button type="button"
                    @click="closeModal('restoreBackup')"
                    class="absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                    aria-label="Close">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>

            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-5 flex items-center justify-center">
                    <div class="p-3 rounded-full bg-primary-100 text-primary-600 dark:bg-primary-500/20 dark:text-primary-400">
                        <x-heroicon-o-arrow-path class="h-6 w-6" />
                    </div>
                </div>

                <h2 id="restore-backup-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('settings.chatbot.confirm_backup_restore') }}
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('settings.chatbot.confirm_backup_restore_description') }}
                </p>

                <!-- Actions -->
                <div class="mt-6 flex w-full items-stretch gap-3">
                    <button type="button"
                            @click="closeModal('restoreBackup')"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">
                        {{ __('Cancel') }}
                    </button>

                    <button type="button"
                            @click="confirmRestoreBackup()"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-primary-600 text-primary-900 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-primary-600 dark:hover:bg-primary-500 dark:focus:ring-offset-gray-900">
                        {{ __('settings.chatbot.actions_menu.restore') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Backup Modal -->
    <div x-show="modals.deleteBackup.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75"
             @click="closeModal('deleteBackup')"
             aria-hidden="true"></div>

        <!-- Modal -->
        <div role="dialog"
             aria-modal="true"
             aria-labelledby="delete-backup-heading"
             class="relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">

            <!-- Close Button -->
            <button type="button"
                    @click="closeModal('deleteBackup')"
                    class="absolute end-4 top-4 inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                    aria-label="Close">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>

            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-5 flex items-center justify-center">
                    <div class="p-3 rounded-full bg-danger-100 text-danger-600 dark:bg-danger-500/20 dark:text-danger-400">
                        <x-heroicon-o-trash class="h-6 w-6" />
                    </div>
                </div>

                <h2 id="delete-backup-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('settings.chatbot.confirm_backup_delete') }}
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('settings.chatbot.confirm_backup_delete_description') }}
                </p>

                <!-- Actions -->
                <div class="mt-6 flex w-full items-stretch gap-3">
                    <button type="button"
                            @click="closeModal('deleteBackup')"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">
                        {{ __('Cancel') }}
                    </button>

                    <button type="button"
                            @click="confirmDeleteBackup()"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-danger-600 text-white hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-danger-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-danger-600 dark:hover:bg-danger-500 dark:focus:ring-offset-gray-900">
                        {{ __('settings.chatbot.actions_menu.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script data-navigate-once>
    // Global modal system
    window.globalModals = {
        deleteComment: { show: false, commentId: null },
        deleteReply: { show: false, replyId: null },
        forceDeleteComment: { show: false, commentId: null },
        forceDeleteReply: { show: false, replyId: null },
        createBackup: { show: false },
        restoreBackup: { show: false, backupId: null },
        deleteBackup: { show: false, backupId: null }
    };
    
    // Show modal function
    window.showGlobalModal = function(type, id) {
        // Close all other modals first
        Object.keys(window.globalModals).forEach(key => {
            window.globalModals[key].show = false;
        });

        // Show the requested modal
        if (window.globalModals[type]) {
            window.globalModals[type].show = true;
            if (type !== 'createBackup') {
                if (type === 'restoreBackup' || type === 'deleteBackup') {
                    window.globalModals[type].backupId = id;
                } else {
                    window.globalModals[type][type.includes('Reply') ? 'replyId' : 'commentId'] = id;
                }
            }

            // Dispatch custom event to notify Alpine.js
            document.dispatchEvent(new CustomEvent('global-modal-opened', {
                detail: { type, id }
            }));
        }
    };

    // Show restore backup modal
    window.showRestoreBackupModal = function(backupId) {
        window.showGlobalModal('restoreBackup', backupId);
    };

    // Show delete backup modal
    window.showDeleteBackupModal = function(backupId) {
        window.showGlobalModal('deleteBackup', backupId);
    };
    
    // Listen for Livewire events (keeping for other modals)
    document.addEventListener('livewire:init', () => {
        Livewire.on('showGlobalModal', (data) => {
            const { type, id } = data;
            window.showGlobalModal(type, id);
        });
    });
    
    // Close modal function
    window.closeGlobalModal = function(type) {
        if (window.globalModals[type]) {
            window.globalModals[type].show = false;
            window.globalModals[type][type.includes('Reply') ? 'replyId' : 'commentId'] = null;
            
            // Dispatch custom event to notify Alpine.js
            document.dispatchEvent(new CustomEvent('global-modal-closed', {
                detail: { type }
            }));
        }
    };
    
    // Alpine.js component for modal container
    window.globalModalContainer = function() {
        return {
            modals: window.globalModals,
            
            init() {
                this.updateModals();
                
                // Listen for custom events to update modals
                document.addEventListener('global-modal-opened', () => {
                    this.updateModals();
                });
                
                document.addEventListener('global-modal-closed', () => {
                    this.updateModals();
                });
                
                // Handle escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.closeAllModals();
                    }
                });
            },
            
            updateModals() {
                this.modals = { ...window.globalModals };
            },
            
            closeModal(type) {
                window.closeGlobalModal(type);
            },
            
            closeAllModals() {
                Object.keys(this.modals).forEach(type => {
                    this.closeModal(type);
                });
            },
            
            confirmDelete() {
                const commentId = this.modals.deleteComment.commentId;
                if (commentId) {
                    // Dispatch event to Livewire component
                    Livewire.dispatch('performDelete', { commentId });
                    this.closeModal('deleteComment');
                }
            },
            
            confirmDeleteReply() {
                const replyId = this.modals.deleteReply.replyId;
                if (replyId) {
                    // Dispatch event to Livewire component
                    Livewire.dispatch('deleteReply', { replyId });
                    this.closeModal('deleteReply');
                }
            },
            
            confirmForceDelete() {
                const commentId = this.modals.forceDeleteComment.commentId;
                if (commentId) {
                    // Dispatch event to Livewire component
                    Livewire.dispatch('forceDeleteComment', { commentId });
                    this.closeModal('forceDeleteComment');
                }
            },
            
            confirmForceDeleteReply() {
                const replyId = this.modals.forceDeleteReply.replyId;
                if (replyId) {
                    // Dispatch event to Livewire component
                    Livewire.dispatch('forceDeleteReply', { replyId });
                    this.closeModal('forceDeleteReply');
                }
            },

            confirmCreateBackup() {
                // Find the ChatbotBackupsTable Livewire component and call its method
                const backupsContainer = document.getElementById('chatbot-backups-table');

                if (backupsContainer) {
                    // Check if the container itself has the wire:id attribute
                    let componentId = backupsContainer.getAttribute('wire:id');

                    if (!componentId) {
                        // Try looking for any element with wire:id inside the container
                        const livewireElement = backupsContainer.querySelector('[wire\\:id]');
                        if (livewireElement) {
                            componentId = livewireElement.getAttribute('wire:id');
                        }
                    }

                    if (componentId) {
                        const component = Livewire.find(componentId);
                        if (component) {
                            component.call('createBackup');
                        }
                    }
                }

                this.closeModal('createBackup');
            },

            confirmRestoreBackup() {
                const backupId = this.modals.restoreBackup.backupId;
                if (backupId) {
                    const backupsContainer = document.getElementById('chatbot-backups-table');
                    if (backupsContainer) {
                        let componentId = backupsContainer.getAttribute('wire:id');
                        if (!componentId) {
                            const livewireElement = backupsContainer.querySelector('[wire\\:id]');
                            if (livewireElement) {
                                componentId = livewireElement.getAttribute('wire:id');
                            }
                        }

                        if (componentId) {
                            const component = Livewire.find(componentId);
                            if (component) {
                                component.call('restoreBackup', backupId);
                            }
                        }
                    }
                }
                this.closeModal('restoreBackup');
            },

            confirmDeleteBackup() {
                const backupId = this.modals.deleteBackup.backupId;
                if (backupId) {
                    const backupsContainer = document.getElementById('chatbot-backups-table');
                    if (backupsContainer) {
                        let componentId = backupsContainer.getAttribute('wire:id');
                        if (!componentId) {
                            const livewireElement = backupsContainer.querySelector('[wire\\:id]');
                            if (livewireElement) {
                                componentId = livewireElement.getAttribute('wire:id');
                            }
                        }

                        if (componentId) {
                            const component = Livewire.find(componentId);
                            if (component) {
                                component.call('deleteBackup', backupId);
                            }
                        }
                    }
                }
                this.closeModal('deleteBackup');
            }
        }
    };
</script>
