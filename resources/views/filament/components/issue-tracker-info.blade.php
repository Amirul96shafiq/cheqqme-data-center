@if($issueTrackerCode && $issueTrackerUrl)
<div class="fi-fo-field-wrp">
    <div class="grid gap-y-2">
        <div class="flex items-center justify-between gap-x-3">

            <!-- Issue Tracker Code Label -->
            <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                {{ __('task.form.issue_tracker_code') }}
            </label>

            <!-- Open Issue Tracker Button -->
            <a 
                href="{{ $issueTrackerUrl }}" 
                target="_blank"
                class="inline-flex items-center gap-x-1.5 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-500 transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                {{ __('task.form.open_issue_tracker') }}
            </a>

        </div>
        <div class="grid auto-cols-fr gap-y-2">
            <div class="fi-fo-field-wrp-input-wrp">
                <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20">

                    <!-- Issue Tracker Code Input -->
                    <div class="flex items-center gap-x-2 px-3 py-2 text-sm text-gray-950 dark:text-white">

                        <!-- Issue Tracker Code -->
                        <span class="font-mono font-semibold text-primary-600 dark:text-primary-400">
                            {{ $issueTrackerCode }}
                        </span>
                        
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endif

