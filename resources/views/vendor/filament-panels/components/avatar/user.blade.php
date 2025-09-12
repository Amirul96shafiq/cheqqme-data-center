@props([
    'user' => filament()->auth()->user(),
])

<x-filament::avatar
    :src="filament()->getUserAvatarUrl($user)"
    :alt="__('filament-panels::layout.avatar.alt', ['name' => filament()->getUserName($user)])"
    :circular="true"
    size="h-9 w-9"
    :attributes="
        \Filament\Support\prepare_inherited_attributes($attributes)
            ->class(['fi-user-avatar border-2 border-primary-500 dark:border-primary-600 hover:border-primary-300 hover:dark:border-primary-500 rounded transition-colors duration-75'])
    "
/>
