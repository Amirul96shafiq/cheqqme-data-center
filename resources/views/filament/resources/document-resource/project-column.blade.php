@php
    $record = $getRecord();
    $project = $record->project;
@endphp

@if (!$project)
    <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
@else
    @php
        $projectTitle = $project->title;
        $displayTitle = \Illuminate\Support\Str::limit($projectTitle, 20, '...');
        $projectUrl = $record->project_id ? \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $record->project_id]) : null;
    @endphp
    @if(empty($projectTitle))
        <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
    @else
        @if($projectUrl)
            <a href="{{ $projectUrl }}" 
               target="_blank" 
               class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
               title="{{ $projectTitle }}">
                {{ $displayTitle }}
            </a>
        @else
            <span class="text-sm text-gray-900 dark:text-white" title="{{ $projectTitle }}">
                {{ $displayTitle }}
            </span>
        @endif
    @endif
@endif

