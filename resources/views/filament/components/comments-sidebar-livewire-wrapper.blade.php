<!-- Comments Sidebar Livewire Wrapper -->
@if($taskId)
    <livewire:task-comments :task-id="$taskId" wire:key="task-comments-{{ $taskId }}" />
    {{-- Removed old Livewire dropdown in favor of Alpine.js version --}}
    <x-user-mention-dropdown-alpine />
@else
    <!-- Task not loaded -->
    <div class="text-xs text-gray-500 p-4">{{ __('comments.sidebar.task_not_loaded') }}</div>
@endif
