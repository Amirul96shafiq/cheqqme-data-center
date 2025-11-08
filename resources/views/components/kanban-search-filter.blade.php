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
    'priorityFilter' => [],
    'cardTypeFilter' => 'all',
    'showFeaturedImages' => true,
])

@php
$currentUserId = auth()->id();

$usersCollection = \App\Models\User::withTrashed()
    ->orderByRaw('COALESCE(name, username) ASC')
    ->get();

$usersForFilter = $usersCollection
    ->sortBy(function ($user) use ($currentUserId) {
        $baseLabel = $user->name ?: ($user->username ?: __('action.form.user_with_id', ['id' => $user->id]));

        return [
            $user->id === $currentUserId ? 0 : 1,
            mb_strtolower($baseLabel),
        ];
    })
    ->mapWithKeys(function ($user) use ($currentUserId) {
        $label = $user->id === $currentUserId
            ? __('action.filter.current_user')
            : ($user->name ?: ($user->username ?: __('action.form.user_with_id', ['id' => $user->id])));

        if ($user->deleted_at) {
            $label .= __('action.form.deleted_suffix');
        }

        return [$user->id => $label];
    })
    ->toArray();
@endphp

<div
     x-data="globalKanbanFilter()" 
     x-init="init()"
     data-initial-search="{{ $search }}"
     data-initial-assigned-to="{{ json_encode($assignedToFilter) }}"
     data-initial-users="{{ json_encode($usersForFilter, JSON_UNESCAPED_UNICODE) }}"
     data-initial-due-date-preset="{{ $dueDatePreset }}"
     data-initial-due-date-from="{{ $dueDateFrom }}"
     data-initial-due-date-to="{{ $dueDateTo }}"
     data-initial-priority-filter="{{ json_encode($priorityFilter) }}"
     data-initial-card-type="{{ $cardTypeFilter }}">
    <div class="flex items-center justify-start sm:justify-end gap-4">
        
        {{-- Board viewers beside search (unified component, full width) --}}
        <x-viewers-banner channel="board-viewers" id="action-board" :fullWidth="true" />

        {{-- Search input --}}
        <div class="relative">

           <!-- Search icon (prefix) -->
           <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
               <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 dark:text-gray-500" />
           </div>
           
           <!-- Search bar-->
           <input
               type="text"
               x-model="globalSearch"
               @input="handleSearchInput()"
               placeholder="{{ $placeholder ?: __('action.search_placeholder') }}"
                class="w-[237px] py-2 pl-10 pr-12 text-sm bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-lg text-gray-600 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200 hover:bg-white/40 dark:hover:bg-gray-800/40 focus:bg-white/40 dark:focus:bg-gray-800/40 focus:ring-1 focus:ring-primary-500"
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
                <x-tooltip :text="__('action.filter_tasks')" position="left" align="center">
                <button
                        @click="filterOpen = !filterOpen"
                    class="flex items-center justify-center w-10 h-10 bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-lg text-gray-400 dark:text-gray-400 hover:bg-white/40 dark:hover:bg-gray-800/40 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200 focus:ring-1 focus:ring-primary-500"
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
                    class="fixed sm:absolute top-10 sm:top-full left-1/2 sm:right-0 -translate-x-1/2 sm:-translate-x-full mt-0 sm:mt-2 z-50 w-80"
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
                                        <x-heroicon-m-chevron-down 
                                            class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                                            ::class="{ 'rotate-180': assignedDropdownOpen }"
                                        />
                                    </span>
                                </button>
                                
                                <x-dropdown-panel is-open="assignedDropdownOpen">
                                    <!-- Users List Section -->
                                    <div class="p-4">
                                    <div class="max-h-48 overflow-y-auto space-y-1">
                                    @foreach($usersForFilter as $userId => $userLabel)
                                                <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <input 
                                                    type="checkbox" 
                                                    value="{{ $userId }}"
                                                    x-model="assignedToFilter"
                                                        @change="handleAssignedFilterChange()"
                                                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                                >
                                                    <span class="text-gray-700 dark:text-gray-300 flex-1">
                                                    {{ $userLabel }}
                                                </span>
                                            </label>
                                    @endforeach
                                </div>
                            </div>
                                </x-dropdown-panel>
                            </div>
                            
                            <!-- Selected Users Display -->
                            <div x-show="assignedToFilter.length > 0" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700" x-data="{ showAllUsers: false }">
                                <div class="flex items-center justify-between mb-2">
                            
                            <!-- Selected Users Display -->
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('action.filter.selected_users') }}</div>

                                    <!-- Show All/Less Button -->
                                    <button 
                                        x-show="assignedToFilter.length > 4"
                                        @click="showAllUsers = !showAllUsers"
                                        type="button"
                                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium"
                                        x-text="showAllUsers ? '{{ __('action.show_less') }}' : '{{ __('action.show_all') }}'"
                                    ></button>

                                </div>

                                <!-- Selected Users List -->
                                <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                                    <template x-for="(userId, index) in assignedToFilter" :key="userId">
                                        <span 
                                            x-show="showAllUsers || index < 4"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-md"
                                        >
                                            <span x-text="getUserById(userId)"></span>
                                            <button @click="removeAssignedUser(userId)" class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <!-- More Button -->
                                    <span 
                                        x-show="!showAllUsers && assignedToFilter.length > 4"
                                        class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-md font-medium"
                                        x-text="'+' + (assignedToFilter.length - 4) + ' {{ __('action.more') }}'"
                                    ></span>
                                    
                                </div>

                            </div>

                        </div>

                        <!-- Card Type Filter -->
                        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                {{ __('action.filter.card_type') }}
                            </label>
                            
                            <!-- Custom Dropdown -->
                            <div class="relative" @click.outside="cardTypeDropdownOpen = false">
                                <button
                                    @click="cardTypeDropdownOpen = !cardTypeDropdownOpen"
                                    type="button"
                                    class="relative w-full cursor-default rounded-lg bg-white dark:bg-gray-800 py-2 pl-3 pr-10 text-left ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:text-sm"
                                >
                                    <span class="block truncate text-gray-900 dark:text-white">
                                        <span x-show="cardTypeFilter === 'all'" class="text-gray-500 dark:text-gray-400">{{ __('action.filter.select_card_type') }}</span>
                                        <span x-show="cardTypeFilter === 'tasks'" x-text="'{{ __('action.filter.card_type_tasks') }}'"></span>
                                        <span x-show="cardTypeFilter === 'issue_trackers'" x-text="'{{ __('action.filter.card_type_issue_trackers') }}'"></span>
                                    </span>
                                    <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                        <x-heroicon-m-chevron-down 
                                            class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                                            ::class="{ 'rotate-180': cardTypeDropdownOpen }"
                                        />
                                    </span>
                                </button>
                                
                                <x-dropdown-panel is-open="cardTypeDropdownOpen">
                                    <!-- Card Type List Section -->
                                    <div class="p-4">
                                        <div class="max-h-48 overflow-y-auto space-y-1">
                                            <!-- All Cards -->
                                            <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <input 
                                                    type="checkbox" 
                                                    value="all"
                                                    :checked="cardTypeFilter === 'all'"
                                                    @change="handleCardTypeFilterChange('all')"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                                >
                                                <span class="text-gray-700 dark:text-gray-300 flex-1">
                                                    {{ __('action.filter.card_type_all') }}
                                                </span>
                                            </label>
                                            
                                            <!-- Tasks Only -->
                                            <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <input 
                                                    type="checkbox" 
                                                    value="tasks"
                                                    :checked="cardTypeFilter === 'tasks'"
                                                    @change="handleCardTypeFilterChange('tasks')"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                                >
                                                <span class="text-gray-700 dark:text-gray-300 flex-1">
                                                    {{ __('action.filter.card_type_tasks') }}
                                                </span>
                                            </label>
                                            
                                            <!-- Issue Trackers -->
                                            <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <input 
                                                    type="checkbox" 
                                                    value="issue_trackers"
                                                    :checked="cardTypeFilter === 'issue_trackers'"
                                                    @change="handleCardTypeFilterChange('issue_trackers')"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                                >
                                                <span class="text-gray-700 dark:text-gray-300 flex-1">
                                                    {{ __('action.filter.card_type_issue_trackers') }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </x-dropdown-panel>
                            </div>
                            
                            <!-- Selected Card Type Display -->
                            <div x-show="cardTypeFilter !== 'all'" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('action.filter.selected_card_type') }}</div>
                                <div class="flex flex-wrap gap-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-md">
                                        <span x-text="getCardTypeLabel(cardTypeFilter)"></span>
                                        <button @click="clearCardTypeFilter()" class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </span>
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
                                        <x-heroicon-m-chevron-down 
                                            class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                                            ::class="{ 'rotate-180': dueDateDropdownOpen }"
                                        />
                                    </span>
                                </button>
                                
                                <x-dropdown-panel is-open="dueDateDropdownOpen">
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
                                                    x-collapse
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
                                                    x-collapse
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
                                </x-dropdown-panel>
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

                        <!-- Priority Filter -->
                        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                {{ __('action.filter.priority') }}
                            </label>
                            
                            <!-- Custom Dropdown -->
                            <div class="relative" @click.outside="priorityDropdownOpen = false">
                                <button
                                    @click="priorityDropdownOpen = !priorityDropdownOpen"
                                    type="button"
                                    class="relative w-full cursor-default rounded-lg bg-white dark:bg-gray-800 py-2 pl-3 pr-10 text-left ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:text-sm"
                                >
                                    <span class="block truncate text-gray-900 dark:text-white">
                                        <span x-show="priorityFilter.length === 0" class="text-gray-500 dark:text-gray-400">{{ __('action.filter.select_priority') }}</span>
                                        <span x-show="priorityFilter.length === 1" x-text="priorityFilter[0]"></span>
                                        <span x-show="priorityFilter.length > 1" x-text="priorityFilter.length + ' {{ __('action.filter.priorities_selected') }}'"></span>
                                    </span>
                                    <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                        <x-heroicon-m-chevron-down 
                                            class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                                            ::class="{ 'rotate-180': priorityDropdownOpen }"
                                        />
                                    </span>
                                </button>
                                
                                <x-dropdown-panel is-open="priorityDropdownOpen">
                                    <!-- Priority List Section -->
                                    <div class="p-4">
                                        <div class="max-h-48 overflow-y-auto space-y-1">
                                            
                                            <!-- High Priority -->
                                            <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <input 
                                                    type="checkbox" 
                                                    value="high"
                                                    x-model="priorityFilter"
                                                    @change="handlePriorityFilterChange()"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                                >
                                                <span class="flex items-center text-gray-700 dark:text-gray-300 flex-1">
                                                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                                                    {{ __('action.filter.priority_high') }}
                                                </span>
                                            </label>
                                            
                                            <!-- Medium Priority -->
                                            <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <input 
                                                    type="checkbox" 
                                                    value="medium"
                                                    x-model="priorityFilter"
                                                    @change="handlePriorityFilterChange()"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                                >
                                                <span class="flex items-center text-gray-700 dark:text-gray-300 flex-1">
                                                    <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                                                    {{ __('action.filter.priority_medium') }}
                                                </span>
                                            </label>
                                            
                                            <!-- Low Priority -->
                                            <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <input 
                                                    type="checkbox" 
                                                    value="low"
                                                    x-model="priorityFilter"
                                                    @change="handlePriorityFilterChange()"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                                >
                                                <span class="flex items-center text-gray-700 dark:text-gray-300 flex-1">
                                                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                                                    {{ __('action.filter.priority_low') }}
                                                </span>
                                            </label>
                                            
                                        </div>
                                    </div>
                                </x-dropdown-panel>
                            </div>
                            
                            <!-- Selected Priority Display -->
                            <div x-show="priorityFilter.length > 0" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('action.filter.selected_priority') }}</div>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="priority in priorityFilter" :key="priority">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-md">
                                            <span x-show="priority === 'high'" class="w-2 h-2 bg-red-500 rounded-full"></span>
                                            <span x-show="priority === 'medium'" class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                            <span x-show="priority === 'low'" class="w-2 h-2 bg-green-500 rounded-full"></span>
                                            <span x-text="getPriorityLabel(priority)"></span>
                                            <button @click="removePriority(priority)" class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        @endif

        {{-- Toggle Featured Images Button --}}
        <div class="relative">
            <x-tooltip :text="__('action.toggle_featured_images_tooltip')" position="left" align="center">
                <button
                    wire:click="toggleFeaturedImages"
                    class="flex items-center justify-center w-10 h-10 bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-lg text-gray-400 dark:text-gray-400 hover:bg-white/40 dark:hover:bg-gray-800/40 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200 focus:ring-1 focus:ring-primary-500"
                    type="button"
                    title="{{ __('action.toggle_featured_images_tooltip') }}"
                >
                    @if($showFeaturedImages)
                        <x-heroicon-o-eye class="w-4 h-4" />
                    @else
                        <x-heroicon-o-eye-slash class="w-4 h-4" />
                    @endif
                </button>
            </x-tooltip>
        </div>
        
    </div>
</div>
