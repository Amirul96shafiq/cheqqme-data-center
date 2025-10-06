<div
    id="board-viewers-banner"
    data-board-id="{{ $boardId ?? 'action-board' }}"
    class="mb-3 hidden items-center gap-2 rounded-md bg-warning-50 px-3 py-2 text-warning-700 ring-1 ring-warning-200 dark:bg-warning-500/10 dark:text-warning-300 dark:ring-warning-400/20"
>
    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="font-medium">Currently viewing board:</span>
        <div id="task-viewers-avatars" class="flex -space-x-2"></div>
        <span id="task-viewers-names" class="truncate"></span>
    </div>
</div>

