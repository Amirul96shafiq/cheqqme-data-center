<!-- Filament Task Activity Log -->
@props(['activities' => []])

@if($activities->isEmpty())
    <div class="text-sm text-gray-500 dark:text-gray-400 italic">
        {{ __('task.activity_log.no_activities') }}
    </div>
@else
    <!-- Recent Activity Log -->
    <div class="space-y-2">
        @foreach($activities as $activity)
            <div class="flex items-center justify-center space-x-3 py-2 px-3 bg-gray-100/20 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex-shrink-0">
                    <!-- Activity Icon -->
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                    ">
                        @switch($activity['description'])
                            @case('created')
                                <x-heroicon-o-plus class="w-4 h-4 text-gray-300" />
                                @break
                            @case('updated')
                                <x-heroicon-o-pencil class="w-4 h-4 text-gray-300" />
                                @break
                            @case('deleted')
                                <x-heroicon-o-trash class="w-4 h-4 text-gray-300" />
                                @break
                            @case('restored')
                                <x-heroicon-o-arrow-uturn-left class="w-4 h-4 text-gray-300" />
                                @break
                            @default
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-300" />
                        @endswitch
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <!-- Activity Details -->
                    <div class="flex items-center justify-between">
                        <!-- Activity Causer -->
                        <p class="text-xs font-medium {{ $activity['causer_id'] === auth()->id() ? 'text-primary-600 dark:text-primary-300' : 'text-gray-900 dark:text-white' }}">
                            {{ $activity['causer_id'] === auth()->id() ? 'You' : $activity['causer_name'] }}
                        </p>
                        <!-- Activity Timestamp -->
                        <div class="flex flex-col items-end justify-center text-[11px] text-gray-500 dark:text-gray-400 leading-tight">
                            <span>{{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}</span>
                            <span>{{ \Carbon\Carbon::parse($activity['created_at'])->format('j/n/y, h:i A') }}</span>
                        </div>
                    </div>
                    <!-- Activity Description -->
                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-[-5px]">
                        {{ ucfirst($activity['description']) }}
                        <!-- Activity Changes -->
                        @if($activity['properties'] && $activity['properties']->count() > 0)
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                @php
                                    // Get the changes from the activity properties
                                    $changes = [];
                                    if ($activity['properties']->has('old')) {
                                        $old = $activity['properties']->get('old');
                                        if (is_array($old)) {
                                            foreach ($old as $key => $value) {
                                                $changes[] = $key;
                                            }
                                        }
                                    }
                                    // Get the new values from the activity properties
                                    if ($activity['properties']->has('attributes')) {
                                        $new = $activity['properties']->get('attributes');
                                        if (is_array($new)) {
                                            foreach ($new as $key => $value) {
                                                if (!in_array($key, $changes)) {
                                                    $changes[] = $key;
                                                }
                                            }
                                        }
                                    }
                                    // Display the changes
                                    if (!empty($changes)) {
                                        echo '(' . implode(', ', array_slice($changes, 0, 3)) . (count($changes) > 3 ? '...' : '') . ')';
                                    }
                                @endphp
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        @endforeach

        @if($activities->count() > 50)
            <div class="text-center py-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('task.activity_log.showing_all', ['count' => $activities->count()]) }}
                </p>
            </div>
        @endif
    </div>
@endif
