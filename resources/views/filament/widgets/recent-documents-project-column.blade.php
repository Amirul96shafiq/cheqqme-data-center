@php
    $record = $getRecord();
    $project = $record->project;
@endphp

@if (!$project)
    <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
@else
    @php
        $projectTitle = $project->title;
        $displayTitle = \Illuminate\Support\Str::limit($projectTitle, 10, '...');
    @endphp
    <span class="text-sm text-gray-900 dark:text-white" title="{{ $projectTitle }}">
        {{ $displayTitle }}
    </span>
@endif

