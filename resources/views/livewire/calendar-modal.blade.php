<div class="w-full h-full flex flex-col" x-data="{ 
    selectedDate: null, 
    showEventPopover: false, 
    popoverEvents: [], 
    popoverPosition: { x: 0, y: 0 },
    closeAndOpen(eventData, position) {
        if (this.showEventPopover) {
            this.showEventPopover = false;
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
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            {{ $monthName }}
        </h3>
        
        <div class="flex items-center gap-2">

            {{-- Navigation Buttons --}}
            <div class="flex items-center gap-2">
                <button type="button" 
                        wire:click="previousMonth"
                        class="w-10 h-10 bg-primary-500/80 hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group"
                        aria-label="{{ __('dashboard.calendar.previous_month') }}">
                    <x-heroicon-m-arrow-left class="w-5 h-5 text-primary-900 transition-colors" />
                </button>
                
                {{-- Today Button --}}
                <button type="button" 
                        wire:click="today"
                        class="px-4 py-2 h-10 text-sm font-medium text-primary-900 bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg transition-colors">
                    {{ __('dashboard.calendar.today') }}
                </button>
                
                <button type="button" 
                        wire:click="nextMonth"
                        class="w-10 h-10 bg-primary-500/80 hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group"
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
            <div class="grid grid-cols-7 gap-px bg-gray-100 dark:bg-gray-800 border border-gray-100 dark:border-gray-800 rounded-t-lg overflow-hidden">
                @php
                    $currentDayName = strtolower(now()->format('D')); // Gets current day name in lowercase (e.g., 'tue')
                    $isCurrentMonth = $year === now()->year && $month === now()->month;
                @endphp
                @foreach(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                    <div class="bg-gray-50 dark:bg-gray-800 px-3 py-2 text-center">
                        <span class="text-xs font-semibold {{ ($day === $currentDayName && $isCurrentMonth) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ __("dashboard.calendar.days.{$day}") }}
                        </span>
                    </div>
                @endforeach
            </div>
            
            {{-- Calendar Days --}}
            <div class="border-x border-b border-gray-100 dark:border-gray-800 rounded-b-lg overflow-hidden">
                @foreach($weeks as $weekIndex => $week)
                    <div class="grid grid-cols-7 gap-px bg-gray-100 dark:bg-gray-800">
                        @foreach($week as $day)
                            <div class="bg-white dark:bg-gray-900 min-h-[120px] p-2 flex flex-col border-b border-gray-100 dark:border-gray-800
                                        {{ !$day['is_current_month'] ? 'opacity-40' : '' }}
                                        {{ $day['is_today'] ? 'ring-2 ring-primary-500 ring-inset' : '' }}">
                                
                                {{-- Date Number --}}
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium 
                                                 {{ $day['is_today'] ? 'flex items-center justify-center w-6 h-6 rounded-full bg-primary-500 text-primary-900' : 'text-gray-500 dark:text-gray-200' }}">
                                        {{ $day['date']->day }}
                                    </span>
                                    
                                    @if($day['tasks']->count() + $day['meetings']->count() > 0)
                                        @php
                                            $totalEvents = $day['tasks']->count() + $day['meetings']->count();
                                        @endphp
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $totalEvents }} {{ $totalEvents === 1 ? __('dashboard.calendar.event') : __('dashboard.calendar.events') }}
                                        </span>
                                    @endif
                                    
                                </div>
                                
                                {{-- Events List --}}
                                <div class="flex-1 space-y-1 overflow-y-auto">

                                    {{-- Tasks --}}
                                    @foreach($day['tasks']->take(3) as $task)
                                        <button type="button"
                                                @click="closeAndOpen({{ json_encode([
                                                            'date' => $day['date']->format('l, j/n/y'),
                                                            'tasks' => [['id' => $task->id, 'title' => $task->title, 'priority' => $task->priority, 'type' => 'task', 'is_assigned' => in_array(auth()->id(), $task->assigned_to ?? [])]],
                                                            'meetings' => []
                                                        ]) }}, { x: $event.clientX, y: $event.clientY })"
                                                class="flex items-center px-2 py-1 text-xs rounded transition-colors w-full text-left
                                                       @if(in_array(auth()->id(), $task->assigned_to ?? []))
                                                           @if($task->priority === 'high')
                                                               bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50
                                                           @elseif($task->priority === 'medium')
                                                               bg-yellow-100 text-yellow-700 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:hover:bg-yellow-900/50
                                                           @else
                                                               bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50
                                                           @endif
                                                       @else
                                                           bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800/50
                                                           @if($task->priority === 'high')
                                                               text-red-600 dark:text-red-400
                                                           @elseif($task->priority === 'medium')
                                                               text-yellow-600 dark:text-yellow-400
                                                           @else
                                                               text-green-600 dark:text-green-400
                                                           @endif
                                                       @endif"
                                                title="{{ $task->title }}">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full mr-1.5 flex-shrink-0
                                                        @if($task->priority === 'high')
                                                            bg-red-500
                                                        @elseif($task->priority === 'medium')
                                                            bg-yellow-500
                                                        @else
                                                            bg-green-500
                                                        @endif"></span>
                                            <span class="truncate">{{ Str::limit($task->title, 35) }}</span>
                                        </button>
                                    @endforeach
                                    
                                    {{-- Meetings --}}
                                    @foreach($day['meetings']->take(3 - $day['tasks']->take(3)->count()) as $meeting)
                                        <button type="button"
                                                @click="closeAndOpen({{ json_encode([
                                                            'date' => $day['date']->format('l, j/n/y'),
                                                            'tasks' => [],
                                                            'meetings' => [['id' => $meeting->id, 'title' => $meeting->title, 'time' => $meeting->meeting_start_time->format('g:i A'), 'url' => $meeting->meeting_url, 'type' => 'meeting', 'is_invited' => in_array(auth()->id(), $meeting->user_ids ?? [])]]
                                                        ]) }}, { x: $event.clientX, y: $event.clientY })"
                                                class="flex items-center px-2 py-1 text-xs rounded transition-colors w-full text-left
                                                       @if(in_array(auth()->id(), $meeting->user_ids ?? []))
                                                           bg-teal-100 text-teal-700 hover:bg-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:hover:bg-teal-900/50
                                                       @else
                                                           bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800/50 text-teal-600 dark:text-teal-400
                                                       @endif"
                                                title="{{ $meeting->title }} - {{ $meeting->meeting_start_time->format('g:i A') }}">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 mr-1.5 flex-shrink-0"></span>
                                            <span class="truncate">{{ $meeting->meeting_start_time->format('g:i A') }} {{ Str::limit($meeting->title, 25) }}</span>
                                        </button>
                                    @endforeach
                                    
                                    {{-- More Events Indicator --}}
                                    @php
                                        $totalEvents = $day['tasks']->count() + $day['meetings']->count();
                                        $displayedEvents = min(3, $totalEvents);
                                        $remainingEvents = $totalEvents - $displayedEvents;
                                    @endphp
                                    
                                    @if($remainingEvents > 0)
                                        <button type="button"
                                                @click="closeAndOpen({{ json_encode([
                                                            'date' => $day['date']->format('l, j/n/y'),
                                                            'tasks' => $day['tasks']->map(fn($t) => ['id' => $t->id, 'title' => $t->title, 'priority' => $t->priority, 'type' => 'task', 'is_assigned' => in_array(auth()->id(), $t->assigned_to ?? [])])->values(),
                                                            'meetings' => $day['meetings']->map(fn($m) => ['id' => $m->id, 'title' => $m->title, 'time' => $m->meeting_start_time->format('g:i A'), 'url' => $m->meeting_url, 'type' => 'meeting', 'is_invited' => in_array(auth()->id(), $m->user_ids ?? [])])->values()
                                                        ]) }}, { x: $event.clientX, y: $event.clientY })"
                                                class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 hover:underline font-medium transition-all">
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
    
    
    {{-- Event Popover Section --}}
    <div x-show="showEventPopover"
         @click.away="showEventPopover = false"
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
            <button type="button" @click="showEventPopover = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        
        {{-- Popover Content --}}
        <div class="space-y-3 max-h-64 overflow-y-auto">

            {{-- Tasks Section --}}
            <template x-if="popoverEvents.tasks && popoverEvents.tasks.length > 0">
                <div class="space-y-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('dashboard.calendar.tasks') }}</p>
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
                                <a :href="`{{ url('admin/tasks') }}/${task.id}/edit`"
                                   target="_blank"
                                   class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-400 hover:underline transition-colors">
                                    {{ __('dashboard.calendar.edit') }}
                                </a>
                            </div>
                            <p class="text-sm text-gray-900 dark:text-gray-100" x-text="task.title"></p>
                        </div>
                    </template>
                </div>
            </template>
            
            {{-- Meetings Section --}}
            <template x-if="popoverEvents.meetings && popoverEvents.meetings.length > 0">
                <div class="space-y-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('dashboard.calendar.meetings') }}</p>
                    <template x-for="meeting in popoverEvents.meetings" :key="meeting.id">
                        <div class="px-3 py-2 rounded-lg border-l-4 bg-gray-50 dark:bg-gray-800/50"
                             :class="meeting.is_invited ? 'border-teal-500' : 'border-gray-300 dark:border-gray-700'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] px-2 py-1 rounded-full font-medium bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400" x-text="meeting.time"></span>
                                <div class="flex items-center gap-1">
                                    <a :href="`{{ url('admin/meeting-links') }}/${meeting.id}/edit`"
                                       target="_blank"
                                       class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-400 hover:underline transition-colors">
                                        {{ __('dashboard.calendar.edit') }}
                                    </a>
                                    <a :href="meeting.url"
                                       target="_blank"
                                       class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-400 hover:underline transition-colors">
                                        {{ __('dashboard.calendar.join') }}
                                    </a>
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
