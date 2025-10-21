<div class="w-full h-full flex flex-col" 
     x-data="{ 
        selectedDate: null, 
        showEventPopover: false, 
        popoverEvents: [], 
        popoverPosition: { x: 0, y: 0 },
        scrollNavigationEnabled: false,
        isNavigating: false,
        lastScrollTime: 0,
        scrollDelta: 0,
        isOverPopover: false,
        
        init() {
            // Check if screen is 2xl or larger (1536px+)
            this.checkScreenSize();
            window.addEventListener('resize', () => this.checkScreenSize());
            
            // Add wheel event listener for scroll-based navigation
            this.$el.addEventListener('wheel', (e) => this.handleScrollNavigation(e), { passive: false });
        },
        
        checkScreenSize() {
            this.scrollNavigationEnabled = window.innerWidth >= 1536;
        },
        
        handleScrollNavigation(event) {
            // Only proceed if scroll navigation is enabled (2xl+ screens)
            if (!this.scrollNavigationEnabled) return;
            
            // Don't navigate if cursor is over the popover
            if (this.isOverPopover) return;
            
            // Prevent spam navigation - 800ms cooldown between navigations
            const currentTime = Date.now();
            if (this.isNavigating || (currentTime - this.lastScrollTime) < 800) {
                event.preventDefault();
                return;
            }
            
            // Accumulate scroll delta
            this.scrollDelta += event.deltaY;
            
            // Threshold: require at least 100px scroll to trigger navigation
            const threshold = 100;
            
            if (Math.abs(this.scrollDelta) >= threshold) {
                event.preventDefault();
                
                this.isNavigating = true;
                this.lastScrollTime = currentTime;
                
                // Close any open event popover before navigating
                this.showEventPopover = false;
                this.isOverPopover = false;
                
                if (this.scrollDelta > 0) {
                    // Scrolling down - next month
                    $wire.call('nextMonth');
                } else {
                    // Scrolling up - previous month
                    $wire.call('previousMonth');
                }
                
                // Reset scroll delta
                this.scrollDelta = 0;
                
                // Reset navigation lock after animation completes
                setTimeout(() => {
                    this.isNavigating = false;
                }, 800);
            }
        },
        
        closeAndOpen(eventData, position) {
            if (this.showEventPopover) {
                this.showEventPopover = false;
                this.isOverPopover = false;
                setTimeout(() => {
                    this.popoverEvents = eventData;
                    this.popoverPosition = position;
                    this.showEventPopover = true;
                }, 150);
            } else {
                this.popoverEvents = eventData;
                this.popoverPosition = position;
                this.showEventPopover = true;
            }
        }
    }">
    
    {{-- Calendar Header --}}
    <div class="flex items-center justify-between mb-6">

        {{-- Month Picker --}}
        <div class="relative" 
             @click.away="openMonthPicker = false"
             x-data="{ 
                openMonthPicker: false,
                pickerYear: {{ $year }},
                minYear: {{ now()->year - 5 }},
                maxYear: {{ now()->year + 5 }},
                currentYear: {{ now()->year }},
                currentMonth: {{ now()->month }},
                selectedYear: {{ $year }},
                selectedMonth: {{ $month }},
                togglePicker() {
                    if (this.openMonthPicker) {
                        this.openMonthPicker = false;
                    } else {
                        this.openMonthPicker = true;
                        showEventPopover = false;
                        isOverPopover = false;
                    }
                }
             }"
             x-init="
                pickerYear = {{ $year }};
                selectedYear = {{ $year }};
                selectedMonth = {{ $month }};
                $watch('$wire.year', value => {
                    pickerYear = Math.max(minYear, Math.min(maxYear, value));
                    selectedYear = value;
                });
                $watch('$wire.month', value => {
                    selectedMonth = value;
                });
             ">
            <button @click="togglePicker()" 
                    class="text-sm 2xl:text-xl font-semibold text-gray-900 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2">
                <span wire:loading.remove wire:target="previousMonth,nextMonth,goToMonth">

                    {{-- Compact format (8/25) for small screens --}}
                    <span class="block 2xl:hidden">{{ $month }}/{{ substr($year, -2) }}</span>

                    {{-- Full format (August 2025) for 2xl+ screens --}}
                    <span class="hidden 2xl:block">{{ $monthName }}</span>
                    
                </span>
                <span wire:loading wire:target="previousMonth,nextMonth,goToMonth">
                    {{ __('calendar.calendar.loading') }}
                </span>
                <x-heroicon-m-chevron-down class="h-5 w-5 transition-transform" x-bind:class="openMonthPicker ? 'rotate-180' : ''" />
            </button>
            
            {{-- Month Picker Dropdown --}}
            <div x-show="openMonthPicker" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute left-0 mt-4 w-72 rounded-lg shadow-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 focus:outline-none z-50"
                 style="display: none;">

                <div class="p-4 space-y-4">

                    {{-- Year Navigation --}}
                    <div class="flex items-center justify-between">
                        <button type="button" 
                                @click="pickerYear > {{ now()->year - 5 }} ? pickerYear-- : null"
                                :disabled="pickerYear <= {{ now()->year - 5 }}"
                                :class="pickerYear <= {{ now()->year - 5 }} ? 'opacity-50 cursor-not-allowed' : ''"
                                class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <x-heroicon-m-arrow-left class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                        </button>
                        <span class="text-lg font-semibold transition-colors"
                              :class="pickerYear === {{ now()->year }} ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-gray-100'"
                              x-text="pickerYear"></span>
                        <button type="button" 
                                @click="pickerYear < {{ now()->year + 5 }} ? pickerYear++ : null"
                                :disabled="pickerYear >= {{ now()->year + 5 }}"
                                :class="pickerYear >= {{ now()->year + 5 }} ? 'opacity-50 cursor-not-allowed' : ''"
                                class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <x-heroicon-m-arrow-right class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                        </button>
                    </div>

                    {{-- Month Grid --}}
                    <div class="grid grid-cols-3 gap-2">
                        @php
                            $currentMonth = now()->month;
                            $currentYear = now()->year;
                            $months = [
                                1 => __('calendar.calendar.months.jan'),
                                2 => __('calendar.calendar.months.feb'),
                                3 => __('calendar.calendar.months.mar'),
                                4 => __('calendar.calendar.months.apr'),
                                5 => __('calendar.calendar.months.may'),
                                6 => __('calendar.calendar.months.jun'),
                                7 => __('calendar.calendar.months.jul'),
                                8 => __('calendar.calendar.months.aug'),
                                9 => __('calendar.calendar.months.sep'),
                                10 => __('calendar.calendar.months.oct'),
                                11 => __('calendar.calendar.months.nov'),
                                12 => __('calendar.calendar.months.dec'),
                            ];
                        @endphp
                        @foreach($months as $monthNum => $monthLabel)
                            @php
                                // Determine the CSS classes based on PHP logic
                                $isSelectedMonth = $monthNum === $month;
                                $isCurrentMonth = $monthNum === $currentMonth && $year === $currentYear;
                                
                                $baseClasses = 'px-3 py-2 text-sm font-medium rounded-lg transition-colors ';
                                
                                if ($isSelectedMonth) {
                                    $highlightClasses = 'bg-primary-500 text-primary-900 hover:bg-primary-400';
                                } elseif ($isCurrentMonth) {
                                    $highlightClasses = 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 hover:bg-primary-200 dark:hover:bg-primary-900/50';
                                } else {
                                    $highlightClasses = 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50';
                                }
                                
                                $allClasses = $baseClasses . $highlightClasses;
                            @endphp
                            <button type="button"
                                    @click="pickerYear >= minYear && pickerYear <= maxYear ? $wire.call('goToMonth', {{ $monthNum }}, pickerYear) : null"
                                    :disabled="pickerYear < minYear || pickerYear > maxYear"
                                    :class="pickerYear < minYear || pickerYear > maxYear ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="{{ $allClasses }}">
                                {{ $monthLabel }}
                            </button>
                        @endforeach
                    </div>

                </div>

            </div>
            
        </div>
        
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">

                {{-- Create Dropdown Button --}}
                <div class="relative" x-data="{ open: false }">
                    <x-tooltip text="{{ __('calendar.tooltip.create_event') }}" position="left">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="inline-flex items-center gap-2 px-4 py-2 h-10 text-sm font-medium bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-lg text-gray-400 dark:text-gray-400 hover:bg-white/40 dark:hover:bg-gray-800/40 hover:text-gray-500 dark:hover:text-gray-300">
                            <x-heroicon-o-plus class="h-4 w-4" />
                            <span class="hidden 2xl:inline">{{ __('calendar.calendar.create') }}</span>
                            <x-heroicon-m-chevron-down class="hidden 2xl:block h-4 w-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                        </button>
                    </x-tooltip>

                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute left-0 2xl:right-0 mt-2 w-52 rounded-lg shadow-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 focus:outline-none z-50"
                        style="display: none;">

                        <div class="py-2">

                            <!-- Create Action Task Button -->
                            <a href="{{ route('filament.admin.pages.action-board') }}?create_task=1" 
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors rounded-md mx-2">
                                <x-heroicon-o-rocket-launch class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                {{ __('calendar.calendar.action_task') }}
                            </a>

                            <!-- Create Meeting Link Button -->
                            <a href="{{ route('filament.admin.resources.meeting-links.create') }}" 
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors rounded-md mx-2">
                                <x-heroicon-o-video-camera class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                {{ __('calendar.calendar.meeting_link') }}
                            </a>

                        </div>

                    </div>
                    
                </div>

                {{-- Filter Button --}}
                <div class="relative" x-data="{ filterOpen: false }" @click.outside="filterOpen = false">
                    <x-tooltip text="{{ __('calendar.tooltip.filter_events') }}" position="left">
                        <button
                            @click="filterOpen = !filterOpen"
                            class="flex items-center justify-center w-10 h-10 bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-lg text-gray-400 dark:text-gray-400 hover:bg-white/40 dark:hover:bg-gray-800/40 hover:text-gray-500 dark:hover:text-gray-300"
                            title="{{ __('calendar.calendar.filter') }}"
                        >
                            <x-heroicon-m-funnel class="w-4 h-4" />
                        </button>
                    </x-tooltip>

                    <!-- Filter Dropdown -->
                    <div 
                        x-show="filterOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 2xl:right-0 mt-2 w-56 rounded-lg shadow-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 focus:outline-none z-50"
                        style="display: none;"
                    >
                        <!-- Filter Header -->
                        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('calendar.calendar.filter_events') }}</h3>
                            <button
                                wire:click="clearTypeFilter"
                                class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium"
                            >
                                {{ __('calendar.calendar.show_all') }}
                            </button>
                        </div>
                        
                        <!-- Filter Options -->
                        <div class="p-2 space-y-2">
                            
                            <!-- Task Filter -->
                            <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                                <input 
                                    type="checkbox" 
                                    value="task"
                                    wire:model.live="typeFilter"
                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                >
                                <span class="flex items-center text-gray-700 dark:text-gray-300 flex-1">
                                    <x-heroicon-o-rocket-launch class="w-4 h-4 text-gray-500 dark:text-gray-400 mr-2" />
                                    {{ __('calendar.calendar.action_task') }}
                                </span>
                            </label>
                            
                            <!-- Meeting Filter -->
                            <label class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                                <input 
                                    type="checkbox" 
                                    value="meeting"
                                    wire:model.live="typeFilter"
                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 mr-3"
                                >
                                <span class="flex items-center text-gray-700 dark:text-gray-300 flex-1">
                                    <x-heroicon-o-video-camera class="w-4 h-4 text-gray-500 dark:text-gray-400 mr-2" />
                                    {{ __('calendar.calendar.meeting_link') }}
                                </span>
                            </label>
                            
                        </div>

                    </div>

                </div>
            </div>

            {{-- Navigation Buttons --}}
            <div class="flex items-center gap-2">
                
                {{-- Previous Month Button --}}
                <x-tooltip text="{{ __('calendar.tooltip.previous_month', ['month' => $this->previousMonthName]) }}" position="left">
                    <button type="button" 
                            wire:click="previousMonth"
                            @click="showEventPopover = false; isOverPopover = false"
                            class="w-10 h-10 bg-primary-500/80 hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group"
                            aria-label="{{ __('calendar.calendar.previous_month') }}">
                        <x-heroicon-m-arrow-left wire:loading.remove wire:target="previousMonth" class="w-5 h-5 text-primary-900 transition-colors" />
                        <x-heroicon-o-arrow-path wire:loading wire:target="previousMonth" class="w-5 h-5 text-primary-900 animate-spin" />
                    </button>
                </x-tooltip>

                {{-- Today Button --}}
                <x-tooltip text="{{ __('calendar.tooltip.jump_today') }}" position="left">
                    <button type="button" 
                            wire:click="today"
                            @click="showEventPopover = false; isOverPopover = false"
                            @disabled($this->isViewingToday)
                            class="px-4 py-2 w-20 h-10 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2
                                text-primary-900 bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 {{ $this->isViewingToday ? 'cursor-not-allowed opacity-60' : '' }}">
                        <span wire:loading.remove wire:target="today">{{ __('calendar.calendar.today') }}</span>
                        <x-heroicon-o-arrow-path wire:loading wire:target="today" class="w-5 h-5 text-primary-900 animate-spin" />
                    </button>
                </x-tooltip>
                
                {{-- Next Month Button --}}
                <x-tooltip text="{{ __('calendar.tooltip.next_month', ['month' => $this->nextMonthName]) }}" position="left">
                    <button type="button" 
                            wire:click="nextMonth"
                            @click="showEventPopover = false; isOverPopover = false"
                            class="w-10 h-10 bg-primary-500/80 hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group"
                            aria-label="{{ __('calendar.calendar.next_month') }}">
                        <x-heroicon-m-arrow-right wire:loading.remove wire:target="nextMonth" class="w-5 h-5 text-primary-900 transition-colors" />
                        <x-heroicon-o-arrow-path wire:loading wire:target="nextMonth" class="w-5 h-5 text-primary-900 animate-spin" />
                    </button>
                </x-tooltip>

            </div>

        </div>
    </div>
    
    {{-- Calendar Grid --}}
    <div class="flex-1 overflow-auto relative">
        <div class="min-w-[700px] relative max-h-full">
            
            {{-- Loading State --}}
            <div wire:loading class="absolute inset-0 p-12 2xl:p-0 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 flex items-center justify-center z-20 rounded-lg">
                <div class="flex flex-col items-start 2xl:items-center justify-center space-y-6 w-full h-full">

                    {{-- Loading Spinner --}}
                    <div class="relative">
                        <x-icons.custom-icon name="refresh" class="w-12 h-12 text-primary-500" />
                    </div>
                    
                    {{-- Loading Text --}}
                    <div class="text-left 2xl:text-center space-y-2">
                        <p class="text-base font-medium text-gray-700 dark:text-gray-300">
                            {{ __('calendar.calendar.loading') }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('calendar.calendar.loading_description') }}
                        </p>
                    </div>

                </div>
            </div>
            
            {{-- Day Headers --}}
            <div class="grid grid-cols-7 gap-px bg-gray-100 dark:bg-gray-800 border border-gray-100 dark:border-gray-800 rounded-t-lg overflow-hidden">
                @php
                    $currentDayName = strtolower(now()->format('D')); // Gets current day name in lowercase (e.g., 'tue')
                    $isCurrentMonth = $year === now()->year && $month === now()->month;
                @endphp
                @foreach(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day)
                    <div class="bg-gray-50 dark:bg-gray-800 px-3 py-2 text-center">
                        <span class="text-xs font-semibold {{ ($day === $currentDayName && $isCurrentMonth) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ __("calendar.calendar.days.{$day}") }}
                        </span>
                    </div>
                @endforeach
            </div>
            
            {{-- Calendar Days --}}
            <div class="border-x border-b border-gray-100 dark:border-gray-800 rounded-b-lg overflow-hidden">
                @php
                    $weekCount = count($weeks);
                    $maxHeightPerWeek = $weekCount > 5 ? 'h-[calc((100vh-300px)/6)]' : 'h-[calc((100vh-300px)/5)]';
                @endphp
                @foreach($weeks as $weekIndex => $week)
                    <div class="grid grid-cols-7 gap-px bg-gray-100 dark:bg-gray-800 {{ $maxHeightPerWeek }}">
                        @foreach($week as $day)
                            <div class="bg-white dark:bg-gray-900 p-2 flex flex-col border-b border-gray-100 dark:border-gray-800
                                        {{ !$day['is_current_month'] ? 'opacity-40' : '' }}
                                        {{ $day['is_today'] ? 'ring-2 ring-primary-500 ring-inset' : '' }}">
                                
                                {{-- Date Number --}}
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="text-sm font-medium 
                                                 {{ $day['is_today'] ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-200' }}">
                                        {{ $day['date']->day }}
                                    </span>
                                    
                                    @if($day['tasks']->count() + $day['meetings']->count() > 0)
                                        @php
                                            $totalEvents = $day['tasks']->count() + $day['meetings']->count();
                                        @endphp
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                            {{ $totalEvents }} {{ $totalEvents === 1 ? __('calendar.calendar.event') : __('calendar.calendar.events') }}
                                        </span>
                                    @endif
                                    
                                </div>
                                
                                {{-- Events List --}}
                                <div class="flex-1 space-y-0.5 overflow-y-auto min-h-0">

                                    {{-- Tasks --}}
                                    @foreach($day['tasks']->take(2) as $task)
                                        @php
                                            $isAssigned = in_array(auth()->id(), $task->assigned_to ?? []);
                                        @endphp
                                        <button type="button"
                                                @click="closeAndOpen({{ \Illuminate\Support\Js::from([
                                                            'date' => $day['date']->format('l, j/n/y'),
                                                            'tasks' => [['id' => $task->id, 'title' => $task->title, 'priority' => $task->priority, 'type' => 'task', 'is_assigned' => $isAssigned]],
                                                            'meetings' => []
                                                        ]) }}, { x: $event.clientX, y: $event.clientY })"
                                                class="flex items-center px-1 py-1.5 text-xs rounded transition-colors w-full text-left {{ $this->getTaskClasses($task, $isAssigned) }}"
                                                title="{{ $task->title }}">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full mr-1.5 flex-shrink-0 {{ $this->getPriorityDotClass($task) }}"></span>
                                            <span class="truncate">{{ Str::limit($task->title, 35) }}</span>
                                        </button>
                                    @endforeach
                                    
                                    {{-- Meetings --}}
                                    @foreach($day['meetings']->take(2 - $day['tasks']->take(2)->count()) as $meeting)
                                        @php
                                            $isInvited = in_array(auth()->id(), $meeting->user_ids ?? []);
                                        @endphp
                                        <button type="button"
                                                @click="closeAndOpen({{ \Illuminate\Support\Js::from([
                                                            'date' => $day['date']->format('l, j/n/y'),
                                                            'tasks' => [],
                                                            'meetings' => [['id' => $meeting->id, 'title' => $meeting->title, 'time' => $meeting->meeting_start_time->format('g:i A'), 'url' => $meeting->meeting_url, 'type' => 'meeting', 'is_invited' => $isInvited]]
                                                        ]) }}, { x: $event.clientX, y: $event.clientY })"
                                                class="flex items-center px-1 py-1.5 text-xs rounded transition-colors w-full text-left {{ $this->getMeetingClasses($meeting, $isInvited) }}"
                                                title="{{ $meeting->title }} - {{ $meeting->meeting_start_time->format('g:i A') }}">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 mr-1.5 flex-shrink-0"></span>
                                            <span class="truncate">{{ $meeting->meeting_start_time->format('g:i A') }} {{ Str::limit($meeting->title, 25) }}</span>
                                        </button>
                                    @endforeach
                                    
                                    {{-- More Events Indicator --}}
                                    @php
                                        $totalEvents = $day['tasks']->count() + $day['meetings']->count();
                                        $displayedEvents = min(2, $totalEvents);
                                        $remainingEvents = $totalEvents - $displayedEvents;
                                    @endphp
                                    
                                    @if($remainingEvents > 0)
                                        <button type="button"
                                                @click="closeAndOpen({{ \Illuminate\Support\Js::from([
                                                            'date' => $day['date']->format('l, j/n/y'),
                                                            'tasks' => $day['tasks']->map(fn($t) => ['id' => $t->id, 'title' => $t->title, 'priority' => $t->priority, 'type' => 'task', 'is_assigned' => in_array(auth()->id(), $t->assigned_to ?? [])])->values(),
                                                            'meetings' => $day['meetings']->map(fn($m) => ['id' => $m->id, 'title' => $m->title, 'time' => $m->meeting_start_time->format('g:i A'), 'url' => $m->meeting_url, 'type' => 'meeting', 'is_invited' => in_array(auth()->id(), $m->user_ids ?? [])])->values()
                                                        ]) }}, { x: $event.clientX, y: $event.clientY })"
                                                class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 hover:underline font-medium transition-all">
                                            <x-tooltip text="{{ __('calendar.tooltip.view_more_events') }}" position="right">
                                                +{{ $remainingEvents }} more
                                            </x-tooltip>
                                        </button>
                                    @endif
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

        </div>
    </div>
    
    
    {{-- Event Popover Section --}}
    <div x-show="showEventPopover"
         @click.away="showEventPopover = false; isOverPopover = false"
         @mouseenter="isOverPopover = true"
         @mouseleave="isOverPopover = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed z-50 w-80 bg-white dark:bg-gray-900 rounded-lg shadow-xl ring-1 ring-gray-100 dark:ring-gray-800 p-4 max-h-[80vh]"
         :style="`left: ${Math.max(20, Math.min(popoverPosition.x, window.innerWidth - 340))}px; top: ${Math.min(popoverPosition.y + 10, window.innerHeight - 400)}px; transform: translateX(${popoverPosition.x < 180 ? '0%' : '-50%'});`"
         x-cloak>
        
        {{-- Popover Header --}}
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-gray-900 dark:text-gray-100" x-text="popoverEvents.date"></h4>
            <button type="button" @click="showEventPopover = false; isOverPopover = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        
        {{-- Popover Content --}}
        <div class="space-y-3 max-h-64 overflow-y-auto">

            {{-- Tasks Section --}}
            <template x-if="popoverEvents.tasks && popoverEvents.tasks.length > 0">
                <div class="space-y-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('calendar.calendar.tasks') }}</p>
                    <template x-for="task in popoverEvents.tasks" :key="task.id">
                        <div class="px-3 py-2 rounded-lg border-l-4 bg-gray-50 dark:bg-gray-800/50"
                             :class="{
                                 'border-red-500': task.priority === 'high' && task.is_assigned,
                                 'border-yellow-500': task.priority === 'medium' && task.is_assigned,
                                 'border-green-500': task.priority === 'low' && task.is_assigned,
                                 'border-gray-300 dark:border-gray-700': !task.is_assigned
                             }">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] px-2 py-1 rounded-full font-medium" 
                                      :class="task.priority === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 
                                              task.priority === 'medium' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : 
                                              'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'"
                                      x-text="task.priority === 'high' ? 'High' : task.priority === 'medium' ? 'Medium' : 'Low'"></span>

                                {{-- Edit Task Button --}}
                                <x-tooltip text="{{ __('calendar.tooltip.edit_task') }}" position="left">
                                    <a :href="`{{ url('admin/tasks') }}/${task.id}/edit`"
                                    target="_blank"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-400 hover:underline transition-colors">
                                        {{ __('calendar.calendar.edit') }}
                                    </a>
                                </x-tooltip>
                                
                            </div>
                            <p class="text-sm text-gray-900 dark:text-gray-100" x-text="task.title"></p>
                        </div>
                    </template>
                </div>
            </template>
            
            {{-- Meetings Section --}}
            <template x-if="popoverEvents.meetings && popoverEvents.meetings.length > 0">
                <div class="space-y-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('calendar.calendar.meetings') }}</p>
                    <template x-for="meeting in popoverEvents.meetings" :key="meeting.id">
                        <div class="px-3 py-2 rounded-lg border-l-4 bg-gray-50 dark:bg-gray-800/50"
                             :class="meeting.is_invited ? 'border-teal-500' : 'border-gray-300 dark:border-gray-700'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] px-2 py-1 rounded-full font-medium bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400" x-text="meeting.time"></span>
                                <div class="flex items-center gap-1">

                                    {{-- Edit Meeting Link Button --}}
                                    <x-tooltip text="{{ __('calendar.tooltip.edit_meeting_link') }}" position="left">
                                        <a :href="`{{ url('admin/meeting-links') }}/${meeting.id}/edit`"
                                        target="_blank"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-400 hover:underline transition-colors">
                                            {{ __('calendar.calendar.edit') }}
                                        </a>
                                    </x-tooltip>

                                    {{-- Join Meeting Button --}}
                                    <x-tooltip text="{{ __('calendar.tooltip.join') }}" position="left">
                                        <a :href="meeting.url"
                                        target="_blank"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-400 hover:underline transition-colors">
                                            {{ __('calendar.calendar.join') }}
                                        </a>
                                    </x-tooltip>

                                </div>
                            </div>
                            <p class="text-sm text-gray-900 dark:text-gray-100" x-text="meeting.title"></p>
                        </div>
                    </template>
                </div>
            </template>

        </div>

    </div>
    
</div>
