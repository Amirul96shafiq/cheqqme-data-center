<div class="w-full h-full flex flex-col" x-data="{ selectedDate: null, showEventPopover: false, popoverEvents: [], popoverPosition: { x: 0, y: 0 } }">
    
    {{-- Calendar Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            {{ $monthName }}
        </h3>
        
        <div class="flex items-center gap-2">

            {{-- Today Button --}}
            <button type="button" 
                    wire:click="today"
                    class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                {{ __('dashboard.calendar.today') }}
            </button>
            
            {{-- Navigation Buttons --}}
            <div class="flex items-center gap-3">
                <button type="button" 
                        wire:click="previousMonth"
                        class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group"
                        aria-label="{{ __('dashboard.calendar.previous_month') }}">
                    <x-heroicon-m-arrow-left class="w-5 h-5 text-primary-900 transition-colors" />
                </button>
                
                <button type="button" 
                        wire:click="nextMonth"
                        class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group"
                        aria-label="{{ __('dashboard.calendar.next_month') }}">
                    <x-heroicon-m-arrow-right class="w-5 h-5 text-primary-900 transition-colors" />
                </button>
            </div>

        </div>
    </div>
    
    {{-- Calendar Grid --}}
    <div class="flex-1 overflow-auto relative">
        <div class="min-w-[700px] relative">
            
            {{-- Loading State --}}
            <div wire:loading class="absolute inset-0 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 flex items-center justify-center z-20 rounded-lg">
                <div class="flex flex-col items-center justify-center space-y-6 w-full h-full">

                    {{-- Loading Spinner --}}
                    <div class="relative">
                        <x-icons.custom-icon name="refresh" class="w-12 h-12 text-primary-500" />
                    </div>
                    
                    {{-- Loading Text --}}
                    <div class="text-center space-y-2">
                        <p class="text-base font-medium text-gray-700 dark:text-gray-300">
                            {{ __('dashboard.calendar.loading') }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('dashboard.calendar.loading_description') }}
                        </p>
                    </div>

                </div>
            </div>
            
            {{-- Day Headers --}}
            <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-t-lg overflow-hidden">
                @foreach(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                    <div class="bg-gray-50 dark:bg-gray-800 px-3 py-2 text-center">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ __("dashboard.calendar.days.{$day}") }}</span>
                    </div>
                @endforeach
            </div>
            
            {{-- Calendar Days --}}
            <div class="border-x border-b border-gray-200 dark:border-gray-700 rounded-b-lg overflow-hidden">
                @foreach($weeks as $weekIndex => $week)
                    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
                        @foreach($week as $day)
                            <div class="bg-white dark:bg-gray-900 min-h-[120px] p-2 flex flex-col
                                        {{ !$day['is_current_month'] ? 'opacity-40' : '' }}
                                        {{ $day['is_today'] ? 'ring-2 ring-primary-500 ring-inset' : '' }}">
                                
                                {{-- Date Number --}}
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium 
                                                {{ $day['is_today'] ? 'flex items-center justify-center w-6 h-6 rounded-full bg-primary-500 text-white' : 'text-gray-900 dark:text-gray-100' }}">
                                        {{ $day['date']->day }}
                                    </span>
                                    
                                    @if($day['tasks']->count() + $day['meetings']->count() > 0)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $day['tasks']->count() + $day['meetings']->count() }}
                                        </span>
                                    @endif
                                </div>
                                
                                {{-- Events List --}}
                                <div class="flex-1 space-y-1 overflow-y-auto">
                                    {{-- Tasks --}}
                                    @foreach($day['tasks']->take(3) as $task)
                                        <a href="{{ route('filament.admin.resources.tasks.edit', $task) }}"
                                           target="_blank"
                                           class="block px-2 py-1 text-xs rounded truncate transition-colors
                                                  @if($task->priority === 'high')
                                                      bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50
                                                  @elseif($task->priority === 'medium')
                                                      bg-yellow-100 text-yellow-700 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:hover:bg-yellow-900/50
                                                  @else
                                                      bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50
                                                  @endif"
                                           title="{{ $task->title }}">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full mr-1.5
                                                        @if($task->priority === 'high')
                                                            bg-red-500
                                                        @elseif($task->priority === 'medium')
                                                            bg-yellow-500
                                                        @else
                                                            bg-green-500
                                                        @endif"></span>
                                            {{ Str::limit($task->title, 20) }}
                                        </a>
                                    @endforeach
                                    
                                    {{-- Meetings --}}
                                    @foreach($day['meetings']->take(3 - $day['tasks']->take(3)->count()) as $meeting)
                                        <a href="{{ route('filament.admin.resources.meeting-links.edit', $meeting) }}"
                                           target="_blank"
                                           class="block px-2 py-1 text-xs rounded truncate bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors"
                                           title="{{ $meeting->title }} - {{ $meeting->meeting_start_time->format('g:i A') }}">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                            {{ $meeting->meeting_start_time->format('g:i A') }} {{ Str::limit($meeting->title, 15) }}
                                        </a>
                                    @endforeach
                                    
                                    {{-- More Events Indicator --}}
                                    @php
                                        $totalEvents = $day['tasks']->count() + $day['meetings']->count();
                                        $displayedEvents = min(3, $totalEvents);
                                        $remainingEvents = $totalEvents - $displayedEvents;
                                    @endphp
                                    
                                    @if($remainingEvents > 0)
                                        <button type="button"
                                                @click="showEventPopover = true; 
                                                        popoverEvents = {{ json_encode([
                                                            'date' => $day['date']->format('F j, Y'),
                                                            'tasks' => $day['tasks']->map(fn($t) => ['id' => $t->id, 'title' => $t->title, 'priority' => $t->priority, 'type' => 'task'])->values(),
                                                            'meetings' => $day['meetings']->map(fn($m) => ['id' => $m->id, 'title' => $m->title, 'time' => $m->meeting_start_time->format('g:i A'), 'type' => 'meeting'])->values()
                                                        ]) }};
                                                        popoverPosition = { x: $event.clientX, y: $event.clientY }"
                                                class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium">
                                            +{{ $remainingEvents }} more
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
    
    
    {{-- Event Popover --}}
    <div x-show="showEventPopover"
         @click.away="showEventPopover = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed z-50 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 p-4"
         :style="`left: ${popoverPosition.x}px; top: ${popoverPosition.y}px; transform: translate(-50%, 10px);`"
         x-cloak>
        
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-gray-900 dark:text-gray-100" x-text="popoverEvents.date"></h4>
            <button type="button" @click="showEventPopover = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        
        <div class="space-y-2 max-h-96 overflow-y-auto">

            {{-- Tasks --}}
            <template x-if="popoverEvents.tasks && popoverEvents.tasks.length > 0">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('dashboard.calendar.tasks') }}</p>
                    <template x-for="task in popoverEvents.tasks" :key="task.id">
                        <a :href="`{{ route('filament.admin.resources.tasks.index') }}/${task.id}`"
                           target="_blank"
                           class="block px-3 py-2 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                           :class="{
                               'border-l-4 border-red-500': task.priority === 'high',
                               'border-l-4 border-yellow-500': task.priority === 'medium',
                               'border-l-4 border-green-500': task.priority === 'low'
                           }">
                            <span x-text="task.title" class="text-gray-900 dark:text-gray-100"></span>
                            <span x-text="`(${task.priority})`" class="text-xs text-gray-500 dark:text-gray-400 ml-1"></span>
                        </a>
                    </template>
                </div>
            </template>
            
            {{-- Meetings --}}
            <template x-if="popoverEvents.meetings && popoverEvents.meetings.length > 0">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('dashboard.calendar.meetings') }}</p>
                    <template x-for="meeting in popoverEvents.meetings" :key="meeting.id">
                        <a :href="`{{ route('filament.admin.resources.meeting-links.index') }}/${meeting.id}`"
                           target="_blank"
                           class="block px-3 py-2 text-sm rounded-lg border-l-4 border-blue-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <span x-text="meeting.time" class="font-medium text-blue-600 dark:text-blue-400"></span>
                            <span x-text="meeting.title" class="text-gray-900 dark:text-gray-100 ml-2"></span>
                        </a>
                    </template>
                </div>
            </template>

        </div>

    </div>
    
</div>
