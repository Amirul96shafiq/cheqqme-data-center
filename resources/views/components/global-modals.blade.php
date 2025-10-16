@props([])

<style>
[x-cloak] { display: none !important; }
</style>

<!-- Global Modal Container for Global Modals -->
<div id="global-modal-container" 
     x-data="globalModalContainer()"
     x-cloak
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
            <x-close-button 
                @click="closeModal('deleteReply')" 
                size="lg"
                variant="minimal"
                class="absolute end-4 top-4 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                aria-label="Close"
            />
            
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
            <x-close-button 
                @click="closeModal('forceDeleteComment')" 
                size="lg"
                variant="minimal"
                class="absolute end-4 top-4 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                aria-label="Close"
            />
            
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
            <x-close-button 
                @click="closeModal('forceDeleteReply')"
                size="lg"
                variant="minimal"
                class="absolute end-4 top-4 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                aria-label="Close"
            />

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
            <x-close-button 
                @click="closeModal('createBackup')"
                size="lg"
                variant="minimal"
                class="absolute end-4 top-4 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                aria-label="Close"
            />

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
            <x-close-button 
                @click="closeModal('restoreBackup')"
                size="lg"
                variant="minimal"
                class="absolute end-4 top-4 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                aria-label="Close"
            />

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
            <x-close-button 
                @click="closeModal('deleteBackup')"
                size="lg"
                variant="minimal"
                class="absolute end-4 top-4 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                aria-label="Close"
            />

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

    <!-- Download Backup Modal -->
    <div x-show="modals.downloadBackup.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75"
             @click="closeModal('downloadBackup')"
             aria-hidden="true"></div>

        <!-- Modal -->
        <div role="dialog"
             aria-modal="true"
             aria-labelledby="download-backup-heading"
             class="relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">

            <!-- Close Button -->
            <button type="button"
                    @click="closeModal('downloadBackup')"
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
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                        <x-heroicon-o-arrow-down-tray class="h-6 w-6" />
                    </div>
                </div>

                <h2 id="download-backup-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('settings.chatbot.confirm_backup_download') }}
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('settings.chatbot.confirm_backup_download_description') }}
                </p>

                <!-- Actions -->
                <div class="mt-6 flex w-full items-stretch gap-3">
                    <button type="button"
                            @click="closeModal('downloadBackup')"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">
                        {{ __('Cancel') }}
                    </button>

                    <button type="button"
                            @click="confirmDownloadBackup()"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-primary-600 text-primary-900 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-primary-600 dark:hover:bg-primary-500 dark:focus:ring-offset-gray-900">
                        {{ __('settings.chatbot.actions_menu.download') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Conversation Modal -->
    <div x-show="modals.clearConversation.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75"
             @click="closeModal('clearConversation')"
             aria-hidden="true"></div>

        <!-- Modal -->
        <div role="dialog"
             aria-modal="true"
             aria-labelledby="clear-conversation-heading"
             @click.stop
             class="relative w-full max-w-sm md:max-w-md mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 px-6 pt-8 pb-6 pointer-events-auto">

            <!-- Close Button -->
            <x-close-button 
                @click="closeModal('clearConversation')"
                size="lg"
                variant="minimal"
                class="absolute end-4 top-4 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                aria-label="Close"
            />

            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-5 flex items-center justify-center">
                    <div class="p-3 rounded-full bg-danger-100 text-danger-600 dark:bg-danger-500/20 dark:text-danger-400">
                        <x-heroicon-o-trash class="h-6 w-6" />
                    </div>
                </div>

                <h2 id="clear-conversation-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Clear Conversation') }}
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('chatbot.clear_confirmation_message') }}
                </p>

                <!-- Actions -->
                <div class="mt-6 flex w-full items-stretch gap-3">
                    <button type="button"
                            @click="closeModal('clearConversation')"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-500 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900">
                        {{ __('Cancel') }}
                    </button>

                    <button type="button"
                            @click="confirmClearConversation()"
                            class="fi-btn flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-danger-600 text-white hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-danger-500/40 focus:ring-offset-2 focus:ring-offset-white dark:bg-danger-600 dark:hover:bg-danger-500 dark:focus:ring-offset-gray-900">
                        <x-heroicon-o-trash class="w-4 h-4" />
                        {{ __('Clear') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Changelog Modal --}}
    <div x-show="modals.changelog.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-auto">
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75" 
             @click="closeModal('changelog')" 
             aria-hidden="true"></div>
        
        {{-- Modal --}}
        <div role="dialog" 
             aria-modal="true" 
             aria-labelledby="changelog-heading" 
             class="relative w-full max-w-4xl mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 pointer-events-auto min-h-[90vh] max-h-[90vh] overflow-hidden">
            
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-full bg-primary-100 text-primary-600 dark:bg-primary-500/20 dark:text-primary-400">
                        <x-heroicon-o-code-bracket class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 id="changelog-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('changelog.title') }}
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="totalCommits ? '{{ __('changelog.subtitle') }}'.replace(':total', totalCommits) : '{{ __('changelog.loading_subtitle') }}'">
                        </p>
                    </div>
                </div>
                
                {{-- Close Button --}}
                <x-close-button 
                    @click="closeModal('changelog')" 
                    size="lg"
                    variant="minimal"
                    class="focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                />
                
            </div>
            
            {{-- Content - Scrollable --}}
            <div class="overflow-y-auto px-6 py-4" data-drag-scroll data-drag-scroll-speed="1">

                {{-- Loading State --}}
                <div x-show="loading" class="flex flex-col items-center justify-center h-full min-h-[60vh] space-y-6">

                    {{-- Loading Spinner --}}
                    <div class="relative">
                        <x-icons.custom-icon name="refresh" class="w-12 h-12 text-primary-500" />
                    </div>
                    
                    {{-- Loading Text --}}
                    <div class="text-center space-y-2">
                        <p class="text-base font-medium text-gray-700 dark:text-gray-300">
                            {{ __('changelog.loading_commits') }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('changelog.please_wait') }}
                        </p>
                    </div>
                    
                </div>
                
                {{-- Commits List --}}
                <div x-show="!loading && commits.length > 0" class="space-y-0">
                    <template x-for="commit in commits" :key="commit.short_hash">
                        <div class="group border-b border-gray-100 dark:border-gray-800 last:border-b-0 py-4 -mx-6 px-6 transition-colors">
                            
                            {{-- Commit Header (Clickable) --}}
                            <div @click="commit.description && commit.description.length > 0 ? toggleCommitDescription(commit.short_hash) : null"
                                 :class="commit.description && commit.description.length > 0 ? 'cursor-pointer' : ''"
                                 class="flex items-start gap-3 -mx-6 px-6 py-0">

                                {{-- Author Avatar --}}
                                <img :src="commit.author_avatar" 
                                     :alt="commit.author_name"
                                     class="w-6 h-6 rounded-full flex-shrink-0"
                                     draggable="false">
                                
                                {{-- Commit Info --}}
                                <div class="flex-1 min-w-0">

                                    {{-- Tag Badges and Commit Hash --}}
                                    <div class="mb-2 flex flex-wrap gap-1.5">
                                        {{-- Tag Badges --}}
                                        <template x-for="tag in commit.tags" :key="tag">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-normal bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200"
                                                  x-text="tag">
                                            </span>
                                        </template>
                                        
                                        {{-- Commit Hash Badge --}}
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-normal bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200"
                                              x-text="commit.short_hash">
                                        </span>
                                    </div>

                                    {{-- Commit Message --}}
                                    <div class="mb-2 flex items-start gap-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 leading-relaxed flex-1" x-text="commit.message">
                                        </p>
                                    </div>
                                    
                                    {{-- Author & Time --}}
                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium text-gray-700 dark:text-gray-300" x-text="commit.author_name">
                                        </span>
                                        <span>Committed</span>
                                        <time :datetime="commit.date" :title="commit.date_formatted">
                                            <span x-text="commit.date_relative"></span>
                                            <span> • </span>
                                            <span x-text="new Date(commit.date).toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true }).replace(',', '').toUpperCase()"></span>
                                        </time>
                                    </div>

                                </div>
                                
                                {{-- Actions --}}
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    
                                    {{-- Chevron Icon (only show if description exists) --}}
                                    <x-tooltip :text="__('changelog.toggle_commit_description')" position="left">
                                        <button type="button"
                                                x-show="commit.description && commit.description.length > 0"
                                                @click.stop="toggleCommitDescription(commit.short_hash)"
                                                class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-all duration-200"
                                                :class="expandedCommit === commit.short_hash ? 'rotate-180' : 'rotate-0'">
                                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                                        </button>
                                    </x-tooltip>

                                    {{-- View Details Button --}}
                                    <x-tooltip :text="__('changelog.view_commit_details')" position="left">
                                        <button type="button"
                                                @click.stop="showCommitDetail(commit.short_hash)"
                                                class="p-1 text-primary-400 hover:text-primary-600 dark:text-primary-500 dark:hover:text-primary-300 transition-colors">
                                            <x-heroicon-o-code-bracket class="w-4 h-4" />
                                        </button>
                                    </x-tooltip>

                                </div>

                            </div>

                            {{-- Commit Description (Collapsible) --}}
                            <div x-show="expandedCommit === commit.short_hash && commit.description && commit.description.length > 0"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-2"
                                 class="mt-3 pl-9 pr-6">
                                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                                    <p class="font-mono text-xs text-gray-500 dark:text-gray-400 leading-relaxed whitespace-pre-wrap" x-text="commit.description"></p>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>
                
                {{-- Empty State --}}
                <div x-show="!loading && commits.length === 0" class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                        <x-heroicon-o-code-bracket class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('changelog.no_commits_found') }}</p>
                </div>
            </div>
            
            {{-- Pagination --}}
            <div x-show="!loading && pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <span x-text="pagination ? '{{ __('changelog.page_info') }}'.replace(':current', pagination.current_page).replace(':last', pagination.last_page).replace(':total', pagination.total) : ''"></span>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        
                        {{-- Previous Page --}}
                        <button x-show="pagination && pagination.current_page > 1"
                                @click="loadPage(pagination.current_page - 1)"
                                :aria-label="'{{ __('changelog.previous_page') }}'"
                                class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group">
                            <x-heroicon-o-arrow-left class="w-5 h-5 text-primary-900 transition-colors" />
                        </button>
                        <button x-show="pagination && pagination.current_page === 1" disabled :aria-label="'{{ __('changelog.previous_page') }}'" class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center cursor-not-allowed opacity-50">
                            <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </button>
                        
                        {{-- Next Page --}}
                        <button x-show="pagination && pagination.current_page < pagination.last_page"
                                @click="loadPage(pagination.current_page + 1)"
                                :aria-label="'{{ __('changelog.next_page') }}'"
                                class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group">
                            <x-heroicon-o-arrow-right class="w-5 h-5 text-primary-900 transition-colors" />
                        </button>
                        <button x-show="pagination && pagination.current_page === pagination.last_page" disabled :aria-label="'{{ __('changelog.next_page') }}'" class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center cursor-not-allowed opacity-50">
                            <x-heroicon-o-arrow-right class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </button>
                    </div>
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
        deleteBackup: { show: false, backupId: null },
        downloadBackup: { show: false, backupId: null },
        clearConversation: { show: false },
        changelog: { show: false }
    };
    
    // Force close all modals on page load
    document.addEventListener('DOMContentLoaded', function() {
        // onsole.log('DOM loaded - forcing all modals closed');
        Object.keys(window.globalModals).forEach(key => {
            window.globalModals[key].show = false;
        });
    });
    
    // Show modal function
    window.showGlobalModal = function(type, id) {
        // Debug: Log modal show request
        // console.log('showGlobalModal called:', { type, id });
        // console.log('Available modals:', window.globalModals);
        
        // Close all other modals first
        Object.keys(window.globalModals).forEach(key => {
            window.globalModals[key].show = false;
        });

        // Show the requested modal
        if (window.globalModals[type]) {
            window.globalModals[type].show = true;
            if (type !== 'createBackup') {
                if (type === 'restoreBackup' || type === 'deleteBackup' || type === 'downloadBackup') {
                    window.globalModals[type].backupId = id;
                } else {
                    window.globalModals[type][type.includes('Reply') ? 'replyId' : 'commentId'] = id;
                }
            }

            // Special handling for changelog modal
            if (type === 'changelog') {
                // Trigger changelog loading after a small delay to ensure modal is visible
                setTimeout(() => {
                    document.dispatchEvent(new CustomEvent('changelog-modal-opened'));
                    
                    // Re-initialize drag-to-scroll for modal content
                    if (window.initDragToScroll) {
                        const modalContainer = document.querySelector('[aria-labelledby="changelog-heading"]');
                        if (modalContainer) {
                            window.initDragToScroll(modalContainer);
                        }
                    }
                }, 100);
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

    // Show download backup modal
    window.showDownloadBackupModal = function(backupId) {
        window.showGlobalModal('downloadBackup', backupId);
    };
    
    // Listen for Livewire events (keeping for other modals)
    document.addEventListener('livewire:init', () => {
        Livewire.on('showGlobalModal', (data) => {
            const { type, id } = data;
            window.showGlobalModal(type, id);
        });
        
        // Listen for the custom "What's News" modal event
        Livewire.on('open-whats-new-modal', () => {
            window.showGlobalModal('changelog');
        });
    });
    
    // Handle "What's News" menu item clicks
    document.addEventListener('click', function(event) {
        // Check if the clicked element contains "What's News" text
        let element = event.target;
        
        // Traverse up the DOM tree to find the menu item
        while (element) {
            if (element.textContent && (element.textContent.includes("What's News") || element.textContent.includes('Apa Yang Baru'))) {
                // Make sure it's actually a menu item
                if (element.closest('[role="menuitem"], .fi-menu-item, [data-filament-menu-item], a[href*="javascript"]')) {
                    // console.log('Found What\'s News menu item:', element);
                    event.preventDefault();
                    event.stopPropagation();
                    if (window.showGlobalModal) {
                        window.showGlobalModal('changelog');
                    } else {
                        console.error('showGlobalModal function not found');
                    }
                    return;
                }
            }
            element = element.parentElement;
        }
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
            
            // Changelog modal data
            loading: true,
            commits: [],
            totalCommits: 0,
            pagination: null,
            currentPage: 1,
            expandedCommit: null,
            
            init() {
                // Debug: Log initial modal state
                // console.log('Global modal container initialized', window.globalModals);
                
                // Force reset all modals to false on init
                Object.keys(window.globalModals).forEach(key => {
                    window.globalModals[key].show = false;
                });
                
                this.updateModals();
                
                // Remove x-cloak after initialization
                this.$el.removeAttribute('x-cloak');
                
                // console.log('Global modal container ready');
                
                // Listen for custom events to update modals
                document.addEventListener('global-modal-opened', (event) => {
                    // console.log('Global modal opened:', event.detail);
                    this.updateModals();
                });
                
                document.addEventListener('global-modal-closed', (event) => {
                        // console.log('Global modal closed:', event.detail);
                    this.updateModals();
                    
                    // Don't reset changelog data when modal is closed
                    // Keep the content so it doesn't show "No commits found"
                });
                
                // Listen for changelog modal opened event
                document.addEventListener('changelog-modal-opened', () => {
                    // Only load data if not already loaded
                    if (this.commits.length === 0) {
                        this.loading = true;
                        this.loadCommits();
                    }
                    // Initialize drag-to-scroll after commits load
                    this.$nextTick(() => {
                        if (window.initDragToScroll) {
                            window.initDragToScroll(this.$el);
                        }
                    });
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
            },

            confirmDownloadBackup() {
                const backupId = this.modals.downloadBackup.backupId;
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
                                component.call('downloadBackup', backupId);
                            }
                        }
                    }
                }
                this.closeModal('downloadBackup');
            },

            confirmClearConversation() {
                // Call the executeClearConversation function from chatbot.js
                if (typeof window.executeClearConversation === 'function') {
                    window.executeClearConversation();
                }
                this.closeModal('clearConversation');
            },

            // Changelog modal methods
            async loadCommits(page = 1) {
                this.loading = true;
                this.currentPage = page;
                
                // Record start time for minimum loading duration
                const startTime = Date.now();
                const minLoadingTime = 1000; // 1 second minimum
                
                try {
                    const response = await fetch(`/changelog?page=${page}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        this.commits = data.commits;
                        this.totalCommits = data.total;
                        this.pagination = data.pagination;
                    } else {
                        console.error('Failed to load commits:', data.error);
                        this.commits = [];
                        this.totalCommits = 0;
                    }
                } catch (error) {
                    console.error('Error loading commits:', error);
                    this.commits = [];
                    this.totalCommits = 0;
                } finally {
                    // Ensure minimum loading time for better UX
                    const elapsedTime = Date.now() - startTime;
                    const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
                    
                    if (remainingTime > 0) {
                        setTimeout(() => {
                            this.loading = false;
                        }, remainingTime);
                    } else {
                        this.loading = false;
                    }
                }
            },

            loadPage(page) {
                this.loadCommits(page);
            },


            copyHash(hash) {
                navigator.clipboard.writeText(hash).then(() => {
                    // console.log('Hash copied:', hash);
                }).catch(err => {
                    console.error('Failed to copy hash:', err);
                });
            },

            showCommitDetail(hash) {
                // Redirect to GitHub commit page
                const githubUrl = `https://github.com/Amirul96shafiq/cheqqme-data-center/commit/${hash}`;
                window.open(githubUrl, '_blank');
            },

            toggleCommitDescription(hash) {
                // Toggle the expanded commit (collapse if already open)
                this.expandedCommit = this.expandedCommit === hash ? null : hash;
            },

            resetChangelog() {
                // Reset changelog data (used for manual reset if needed)
                this.commits = [];
                this.totalCommits = 0;
                this.pagination = null;
                this.currentPage = 1;
                this.loading = true;
            }
        }
    };
</script>
