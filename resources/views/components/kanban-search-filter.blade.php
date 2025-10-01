@props([
    'search' => null,
    'placeholder' => null,
    'clearLabel' => null,
    'wireModel' => 'search',
    'wireClear' => 'clearSearch',
    'showFilter' => false,
    'assignedToFilter' => [],
    'dueDatePreset' => null,
    'dueDateFrom' => null,
    'dueDateTo' => null,
])

@php
$usersForFilter = \App\Models\User::withTrashed()->orderByRaw('COALESCE(name, username) ASC')->get()->mapWithKeys(fn($user) => [$user->id => ($user->name ?: 'User #'.$user->id).($user->deleted_at ? ' (deleted)' : '')])->toArray();
@endphp

<div class="-mb-4" 
     x-data="globalKanbanFilter()" 
     x-init="init()"
     data-initial-search="{{ $search }}"
     data-initial-assigned-to="{{ json_encode($assignedToFilter) }}"
     data-initial-users="{{ json_encode($usersForFilter) }}"
     data-initial-due-date-preset="{{ $dueDatePreset }}"
     data-initial-due-date-from="{{ $dueDateFrom }}"
     data-initial-due-date-to="{{ $dueDateTo }}">
    <div class="flex items-center gap-4">
        <div class="relative">

           <!-- Search icon (prefix) -->
           <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
               <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 dark:text-gray-500" />
           </div>
           
           <!-- Search input -->
           <input
               type="text"
               x-model="globalSearch"
               @input="handleSearchInput()"
               placeholder="{{ $placeholder ?: __('action.search_placeholder') }}"
                class="w-[300px] py-3 pl-10 pr-12 text-sm bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-xl text-gray-600 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200 hover:bg-white/40 dark:hover:bg-gray-800/40 focus:bg-white/40 dark:focus:bg-gray-800/40 focus:ring-1 focus:ring-primary-500"
               autocomplete="off"
           />
              <!-- Clear button (always visible, conditionally styled) -->
              <button
                  @click="clearSearch()"
                  class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200 focus:outline-none hover:bg-white/20 dark:hover:bg-gray-700/30 disabled:opacity-50 disabled:cursor-not-allowed"
                  type="button"
                  title="{{ $clearLabel ?: __('action.clear_search') }}"
                  x-show="globalSearch && globalSearch.length > 0"
              >
                  <x-heroicon-o-x-mark class="w-4 h-4" />
              </button>
        </div>

        @if($showFilter)

            <!-- Filter Button -->
            <div class="relative" @click.outside="filterOpen = false">
                <x-tooltip :text="__('action.filter_tasks')" position="top" align="center">
                    <button
                        @click="filterOpen = !filterOpen"
                        class="flex items-center justify-center w-12 h-12 bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-xl text-gray-400 dark:text-gray-400 hover:bg-white/40 dark:hover:bg-gray-800/40 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200 focus:ring-1 focus:ring-primary-500"
                        :class="{ 'ring-1 ring-primary-500 dark:ring-offset-gray-800': filterOpen }"
                    >
                        <x-heroicon-m-funnel class="w-4 h-4" />
                    </button>
                </x-tooltip>
                
                <!-- Mobile Backdrop -->
                <div 
                    x-show="filterOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="filterOpen = false"
                    class="fixed inset-0 bg-black bg-opacity-80 z-40 sm:hidden"
                    style="display: none;"
                ></div>

                <!-- Filter Dropdown -->
                <div 
                    x-show="filterOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="fixed sm:absolute top-10 sm:top-full left-1/2 sm:left-0 -translate-x-1/2 sm:translate-x-0 mt-0 sm:mt-2 z-50 w-80"
                    style="display: none;"
                >
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700">

                        <!-- Filter Header -->
                        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('action.filters') }}</h3>
                                        <button
                                            @click="clearFilters()"
                                            class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium"
                                        >
                                            {{ __('action.reset') }}
                                        </button>
                        </div>
                        
                        <!-- Assigned To Filter -->
                        <div class="p-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                {{ __('action.filter.assigned_to') }}
                            </label>
                            
                            <!-- Custom Dropdown -->
                            <div class="relative" @click.outside="assignedDropdownOpen = false">
                                <button
                                    @click="assignedDropdownOpen = !assignedDropdownOpen"
                                                type="button"
                                                class="relative w-full cursor-default rounded-lg bg-white dark:bg-gray-800 py-2 pl-3 pr-10 text-left ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:text-sm"
                                            >
                                                <span class="block truncate text-gray-900 dark:text-white">
                                                    <span x-show="assignedToFilter.length === 0" class="text-gray-500 dark:text-gray-400">{{ __('action.filter.select_users') }}</span>
                                                    <span x-show="assignedToFilter.length === 1" x-text="getUserById(assignedToFilter[0])"></span>
                                                    <span x-show="assignedToFilter.length > 1" x-text="assignedToFilter.length + ' {{ __('action.filter.users_selected') }}'"></span>
                                                </span>
                                                <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                                    <x-heroicon-m-chevron-down class="h-5 w-5 text-gray-400" />
                                                </span>
                                            </button>
                                
                                <!-- Dropdown Panel -->
                                <div 
                                    x-show="assignedDropdownOpen"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute z-[60] top-full mt-1 w-64 overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 focus:outline-none"
                                    style="display: none;"
                                >
                                    <!-- Users List Section -->
                                    <div class="p-4">
                                        <div class="max-h-48 overflow-y-auto space-y-1">
                                            @foreach(\App\Models\User::withTrashed()->orderByRaw('COALESCE(name, username) ASC')->get() as $user)
                                                <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <input 
                                                        type="checkbox" 
                                                        value="{{ $user->id }}"
                                                        x-model="assignedToFilter"
                                                        @change="handleAssignedFilterChange()"
                                                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                                    >
                                                    <span class="text-gray-700 dark:text-gray-300 flex-1">
                                                        {{ $user->name ?: 'User #'.$user->id }}
                                                        @if($user->deleted_at)
                                                            <span class="text-gray-500 dark:text-gray-400">(deleted)</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Selected Users Display -->
                            <div x-show="assignedToFilter.length > 0" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('action.filter.selected_users') }}</div>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="userId in assignedToFilter" :key="userId">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-md">
                                            <span x-text="getUserById(userId)"></span>
                                                <button @click="removeAssignedUser(userId)" class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                </div>
                            </div>

                        </div>

                        <!-- Due Date Filter -->
                        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                {{ __('action.filter.due_date') }}
                            </label>
                            
                            <!-- Custom Dropdown -->
                            <div class="relative" @click.outside="dueDateDropdownOpen = false">
                                <button
                                    @click="dueDateDropdownOpen = !dueDateDropdownOpen"
                                    type="button"
                                    class="relative w-full cursor-default rounded-lg bg-white dark:bg-gray-800 py-2 pl-3 pr-10 text-left ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:text-sm"
                                >
                                    <span class="block truncate text-gray-900 dark:text-white">
                                        <span x-show="!dueDatePreset && !dueDateFrom && !dueDateTo" class="text-gray-500 dark:text-gray-400">{{ __('action.filter.select_due_date') }}</span>
                                        <span x-show="dueDatePreset === 'today'" x-text="'{{ __('action.filter.due_today') }}'"></span>
                                        <span x-show="dueDatePreset === 'week'" x-text="'{{ __('action.filter.due_this_week') }}'"></span>
                                        <span x-show="dueDatePreset === 'month'" x-text="'{{ __('action.filter.due_this_month') }}'"></span>
                                        <span x-show="dueDatePreset === 'year'" x-text="'{{ __('action.filter.due_this_year') }}'"></span>
                                        <span x-show="!dueDatePreset && (dueDateFrom || dueDateTo)" x-text="getDateRangeText()"></span>
                                    </span>
                                    <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                        <x-heroicon-m-chevron-down class="h-5 w-5 text-gray-400" />
                                    </span>
                                </button>
                                
                                <!-- Dropdown Panel -->
                                <div 
                                    x-show="dueDateDropdownOpen"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute z-[60] top-full mt-1 w-64 overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 focus:outline-none"
                                    style="display: none;"
                                >
                                    <!-- Due Date Filter Section -->
                                    <div class="p-4" x-data="{ activeAccordion: null }">
                                        <div class="max-h-48 overflow-y-auto space-y-4">
 
                                            <!-- Quick Filters -->
                                            <div>
                                                <button 
                                                    @click="activeAccordion = activeAccordion === 'quick' ? null : 'quick'"
                                                    type="button"
                                                    class="w-full flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-150"
                                                >
                                                    <span>{{ __('action.filter.quick_filters') }}</span>
                                                    <x-heroicon-m-chevron-down 
                                                        class="w-4 h-4 transition-transform duration-200"
                                                        ::class="{ 'rotate-180': activeAccordion === 'quick' }"
                                                    />
                                                </button>
                                                
                                                <div 
                                                    x-show="activeAccordion === 'quick'"
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    x-transition:leave="transition ease-in duration-150"
                                                    x-transition:leave-start="opacity-100 translate-y-0"
                                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                                    class="p-1 space-y-1"
                                                >

                                                    <!-- Today -->
                                                    <button 
                                                        @click.prevent="toggleDueDatePreset('today')"
                                                        class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150"
                                                        :class="dueDatePreset === 'today' 
                                                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' 
                                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                                    >
                                                        {{ __('action.filter.due_today') }}
                                                    </button>
                                                    
                                                    <!-- This Week -->
                                                    <button 
                                                        @click.prevent="toggleDueDatePreset('week')"
                                                        class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150"
                                                        :class="dueDatePreset === 'week' 
                                                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' 
                                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                                    >
                                                        {{ __('action.filter.due_this_week') }}
                                                    </button>
                                                    
                                                    <!-- This Month -->
                                                    <button 
                                                        @click.prevent="toggleDueDatePreset('month')"
                                                        class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150"
                                                        :class="dueDatePreset === 'month' 
                                                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' 
                                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                                    >
                                                        {{ __('action.filter.due_this_month') }}
                                                    </button>
                                                    
                                                    <!-- This Year -->
                                                    <button 
                                                        @click.prevent="toggleDueDatePreset('year')"
                                                        class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150"
                                                        :class="dueDatePreset === 'year' 
                                                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' 
                                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                                    >
                                                        {{ __('action.filter.due_this_year') }}
                                                    </button>
                                                    
                                                </div>
                                            </div>
                                            
                                            <!-- Divider -->
                                            <div class="border-t border-gray-200 dark:border-gray-700"></div>
                                            
                                            <!-- Custom Date Range -->
                                            <div>
                                                <button 
                                                    @click="activeAccordion = activeAccordion === 'custom' ? null : 'custom'"
                                                    type="button"
                                                    class="w-full flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-150"
                                                >
                                                    <span>{{ __('action.filter.custom_range') }}</span>
                                                    <x-heroicon-m-chevron-down 
                                                        class="w-4 h-4 transition-transform duration-200"
                                                        ::class="{ 'rotate-180': activeAccordion === 'custom' }"
                                                    />
                                                </button>
                                                
                                                <div 
                                                    x-show="activeAccordion === 'custom'"
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    x-transition:leave="transition ease-in duration-150"
                                                    x-transition:leave-start="opacity-100 translate-y-0"
                                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                                    class="p-1 space-y-3"
                                                >
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                            {{ __('action.filter.from_date') }}
                                                        </label>
                                                        <input 
                                                            type="date"
                                                            x-model="dueDateFrom"
                                                            @change="handleDueDateRangeChange()"
                                                            class="w-full rounded-lg bg-white dark:bg-gray-800 border-1 border-gray-200 dark:border-gray-700 px-3 py-2 text-sm ring-0 ring-inset ring-gray-300 dark:ring-gray-600 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                                        />
                                                    </div>
                                                    
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                            {{ __('action.filter.to_date') }}
                                                        </label>
                                                        <input 
                                                            type="date"
                                                            x-model="dueDateTo"
                                                            @change="handleDueDateRangeChange()"
                                                            class="w-full rounded-lg bg-white dark:bg-gray-800 border-1 border-gray-200 dark:border-gray-700 px-3 py-2 text-sm ring-0 ring-inset ring-gray-300 dark:ring-gray-600 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                                        />
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                            
                            <!-- Selected Due Date Display -->
                            <div x-show="dueDatePreset || dueDateFrom || dueDateTo" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('action.filter.selected_due_date') }}</div>
                                <div class="flex flex-wrap gap-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-md">
                                        <span x-text="getDueDateDisplayText()"></span>
                                        <button @click="clearDueDateFilter()" class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        @endif
    </div>
</div>
