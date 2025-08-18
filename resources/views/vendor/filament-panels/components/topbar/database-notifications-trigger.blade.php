	<button
		type="button"
		class="relative inline-flex h-9 w-9 items-center justify-center language-switch-trigger bg-white dark:bg-[rgb(255_255_255_/_0.05)] border border-[rgb(3_7_18_/_0.1)] hover:border-[rgb(3_7_18_/_0.2)] dark:border-[rgb(255_255_255_/_0.2)] dark:hover:border-[rgb(255_255_255_/_0.3)] rounded-lg transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
		aria-label="{{ __('filament-panels::layout.actions.open_database_notifications.label') }}"
        x-data="{}"
        x-on:click="$dispatch('open-modal', { id: 'database-notifications' })"
	>
		<x-heroicon-o-bell class="h-5 w-5 text-primary-600" />

		@if(($unreadNotificationsCount ?? 0) > 0)
			<span class="absolute -top-0.5 -right-0.5 inline-flex min-w-[1rem] h-4 items-center justify-center rounded-full bg-danger-600 px-1 text-[10px] font-medium text-white">
				{{ $unreadNotificationsCount }}
			</span>
		@endif
	</button>

