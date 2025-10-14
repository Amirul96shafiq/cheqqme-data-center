<div class="space-y-6">
    {{-- Meeting Information --}}
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Meeting Information</h3>
        
        <div class="grid grid-cols-1 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Meeting Title</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->title }}</dd>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Platform</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset 
                            @if($record->meeting_platform === 'Google Meet') bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20 @endif">
                            {{ $record->meeting_platform }}
                        </span>
                    </dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Time</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ $record->meeting_start_time?->format('j/n/y, h:i A') ?? '-' }}
                    </dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        @if($record->meeting_duration)
                            {{ match($record->meeting_duration) {
                                30 => '30 minutes',
                                60 => '1 hour',
                                90 => '1 hour 30 minutes',
                                120 => '2 hours',
                                default => $record->meeting_duration . ' minutes'
                            } }}
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </div>
            
            @if($record->meeting_url)
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Meeting URL</dt>
                <dd class="mt-1">
                    <a href="{{ $record->meeting_url }}" target="_blank" class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                        {{ $record->meeting_url }}
                    </a>
                </dd>
            </div>
            @endif
        </div>
    </div>

    {{-- Attendees --}}
    @if($record->user_ids && count($record->user_ids) > 0)
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attendees ({{ count($record->user_ids) }})</h3>
        <div class="flex flex-wrap gap-2">
            @foreach(\App\Models\User::whereIn('id', $record->user_ids)->get() as $user)
                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                    {{ $user->username }}
                </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Additional Information --}}
    @if($record->notes)
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Description</h3>
        <div class="prose prose-sm dark:prose-invert max-w-none">
            {!! $record->notes !!}
        </div>
    </div>
    @endif
</div>

