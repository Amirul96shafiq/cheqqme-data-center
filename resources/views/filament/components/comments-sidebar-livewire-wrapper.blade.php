@if($taskId)
    <livewire:task-comments :task-id="$taskId" wire:key="task-comments-{{ $taskId }}" />
@else
    <div class="text-xs text-gray-500 p-4">Task not loaded.</div>
@endif
