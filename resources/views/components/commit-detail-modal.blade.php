@props(['commit' => null])

{{-- Commit Detail Modal --}}
@if($commit)
<div x-show="modals.commitDetail.show"
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
         @click="closeModal('commitDetail')" 
         aria-hidden="true"></div>
    
    {{-- Modal --}}
    <div role="dialog" 
         aria-modal="true" 
         aria-labelledby="commit-detail-heading" 
         class="relative w-full max-w-4xl mx-auto cursor-default flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 pointer-events-auto max-h-[90vh] overflow-hidden">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-500/20 dark:text-primary-400">
                    <x-heroicon-o-code-bracket class="h-5 w-5" />
                </div>
                <div>
                    <h2 id="commit-detail-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Commit {{ $commit['short_hash'] }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $commit['date']->format('M j, Y g:i A') }}
                    </p>
                </div>
            </div>
            
            {{-- Close Button --}}
            <button type="button" 
                    @click="closeModal('commitDetail')" 
                    class="inline-flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30" 
                    aria-label="Close">
                <svg class="w-5 h-5" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        
        {{-- Content - Scrollable --}}
        <div class="overflow-y-auto px-6 py-4 space-y-6">
            {{-- Commit Message --}}
            <div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ $commit['subject'] }}
                </h3>
                @if($commit['body'])
                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                        {{ $commit['body'] }}
                    </div>
                @endif
            </div>
            
            {{-- Author Info --}}
            <div class="flex items-center gap-3">
                <img src="{{ $commit['author_avatar'] }}" 
                     alt="{{ $commit['author_name'] }}"
                     class="w-10 h-10 rounded-full">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $commit['author_name'] }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $commit['author_email'] }}
                    </p>
                </div>
            </div>
            
            {{-- Changed Files --}}
            @if($commit['files']->isNotEmpty())
                <div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">
                        Changed Files ({{ $commit['files']->count() }})
                    </h3>
                    <div class="space-y-2">
                        @foreach($commit['files'] as $file)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-xs px-2 py-1 rounded font-medium
                                    @if($file['status'] === 'A') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                    @elseif($file['status'] === 'D') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                    @elseif($file['status'] === 'M') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    {{ $file['status'] }}
                                </span>
                                <code class="text-xs font-mono text-gray-700 dark:text-gray-300">
                                    {{ $file['file'] }}
                                </code>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Commit Hash --}}
            <div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Commit Hash
                </h3>
                <div class="flex items-center gap-2">
                    <code class="text-sm font-mono text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
                        {{ $commit['full_hash'] }}
                    </code>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $commit['full_hash'] }}'); $dispatch('notify', {type: 'success', message: 'Full hash copied!'})"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                            title="Copy full hash">
                        <x-heroicon-o-clipboard-document class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
        
        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button"
                    @click="closeModal('commitDetail')"
                    class="w-full fi-btn inline-flex items-center justify-center gap-1.5 rounded-lg px-5 h-10 text-sm font-medium tracking-tight bg-primary-600 text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                Close
            </button>
        </div>
    </div>
</div>
@endif
