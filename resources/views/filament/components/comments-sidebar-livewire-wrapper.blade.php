@if($taskId)
    <livewire:task-comments :task-id="$taskId" wire:key="task-comments-{{ $taskId }}" />
@else
    <div class="text-xs text-gray-500 p-4">{{ __('comments.sidebar.task_not_loaded') }}</div>
@endif
