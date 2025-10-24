<div
    id="task-viewers-banner"
    data-task-id="{{ (int) ($taskId ?? ($record->id ?? $record->getKey() ?? 0)) }}"
    class="mb-3 hidden items-center gap-2 rounded-md bg-amber-50 px-3 py-2 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/20"
>
    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="font-medium">Currently viewing:</span>
        <div id="task-viewers-avatars" class="flex -space-x-2"></div>
        <span id="task-viewers-names" class="truncate"></span>
    </div>
</div>

