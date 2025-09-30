@props([
    'search' => null,
    'placeholder' => null,
    'clearLabel' => null,
    'wireModel' => 'search',
    'wireClear' => 'clearSearch',
    'showFilter' => false
])

<div class="-mb-8 px-4">
    <div class="flex items-center gap-2">
        <div class="relative">

           <!-- Search icon (prefix) -->
           <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
               <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 dark:text-gray-500" />
           </div>
           
           <!-- Search input -->
           <input
               type="text"
               wire:model.live.debounce.300ms="{{ $wireModel }}"
               placeholder="{{ $placeholder ?: __('action.search_placeholder') }}"
                class="w-56 py-3 pl-10 pr-12 text-sm bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-xl text-gray-600 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200 hover:bg-white/40 dark:hover:bg-gray-800/40 focus:bg-white/40 dark:focus:bg-gray-800/40 focus:ring-1 focus:ring-primary-500"
               autocomplete="off"
           />
          @if($search)

              <!-- Clear button -->
              <button
                  wire:click="{{ $wireClear }}"
                  class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200 focus:outline-none hover:bg-white/20 dark:hover:bg-gray-700/30 disabled:opacity-50 disabled:cursor-not-allowed"
                  type="button"
                  title="{{ $clearLabel ?: __('action.clear_search') }}"
                  wire:loading.attr="disabled"
                  wire:target="{{ $wireClear }}"
              >
                  <!-- Loading spinner -->
                  <div wire:loading wire:target="{{ $wireClear }}" class="w-4 h-4">
                      <x-icons.custom-icon name="refresh" class="w-4 h-4" />
                  </div>
                  
                  <!-- Clear icon (hidden when loading) -->
                  <div wire:loading.remove wire:target="{{ $wireClear }}">
                      <x-heroicon-o-x-mark class="w-4 h-4" />
                  </div>
              </button>
          @endif
        </div>

        @if($showFilter)
            <!-- Filter Button -->
            <div class="relative" x-data="filterData()" @click.outside="open = false">
                <button
                    @click="open = !open"
                    class="flex items-center justify-center w-12 h-12 bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-xl text-gray-600 dark:text-gray-100 hover:bg-white/40 dark:hover:bg-gray-800/40 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200 focus:ring-1 focus:ring-primary-500"
                    :class="{ 'ring-1 ring-primary-500 dark:ring-offset-gray-800': open }"
                    title="{{ __('action.filter_tasks') }}"
                >
                    <x-heroicon-o-funnel class="w-5 h-5" />
                </button>
                
                <!-- Filter Dropdown -->
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute top-full mt-2 left-0 z-50 min-w-[420px]"
                    style="display: none;"
                >
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2">
                        <!-- Filter Header -->
                        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('action.filter_by') }}</h3>
                        </div>
                        
                        <!-- Assigned To Filter -->
                        <div class="px-4 py-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('action.filter.assigned_to') }}
                            </label>
                            
                            <!-- Selected Users Display -->
                            <div x-show="assignedToFilter.length > 0" class="mb-2">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="userId in assignedToFilter" :key="userId">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-md">
                                            <span x-text="getUserById(userId)"></span>
                                            <button @click="removeUser(userId)" class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- User Selection -->
                            <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700">
                                @foreach(\App\Models\User::withTrashed()->orderBy('username')->get() as $user)
                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 rounded px-2 py-1">
                                        <input 
                                            type="checkbox" 
                                            value="{{ $user->id }}"
                                            x-model="assignedToFilter"
                                            @change="applyFilter()"
                                            class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600"
                                        >
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            {{ $user->name ?: 'User #'.$user->id }}{{ $user->deleted_at ? ' (deleted)' : '' }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Filter Actions -->
                        <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 flex gap-2">
                            <button
                                @click="clearFilter()"
                                class="flex-1 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 rounded transition-colors"
                            >
                                {{ __('action.clear_filter') }}
                            </button>
                            <button
                                @click="open = false"
                                class="flex-1 px-3 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/10 rounded transition-colors"
                            >
                                {{ __('action.close') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Dropdown Arrow -->
                    <div class="absolute bottom-full right-4 w-0 h-0 border-l-4 border-r-4 border-b-4 border-transparent border-b-white dark:border-b-gray-800"></div>
                </div>
            </div>
        @endif
    </div>
</div>

@if($showFilter)
@php
$usersForFilter = \App\Models\User::withTrashed()->orderBy('name')->get()->mapWithKeys(fn($user) => [$user->id => ($user->name ?: 'User #'.$user->id).($user->deleted_at ? ' (deleted)' : '')])->toArray();
@endphp
<script>
function filterData() {
    return {
        open: false,
        assignedToFilter: [],
        users: @json($usersForFilter),
        applyFilter() {
            // Dispatch filter event to update kanban board
            const event = new CustomEvent('action-board-filter', {
                detail: {
                    assignedTo: this.assignedToFilter
                }
            });
            window.dispatchEvent(event);
            document.dispatchEvent(event);
        },
        clearFilter() {
            this.assignedToFilter = [];
            this.applyFilter();
        },
        getUserById(userId) {
            return this.users[userId] || 'Unknown User';
        },
        removeUser(userId) {
            this.assignedToFilter = this.assignedToFilter.filter(id => id != userId);
            this.applyFilter();
        }
    }
}
</script>
@endif
