<!-- User Mention Dropdown -->
<div>
    <!-- Show dropdown if it exists and has users -->
    @if($showDropdown && count($users) > 0)
        <!-- Backdrop for click outside -->
        <div 
            class="fixed inset-0 z-40"
            wire:click="hideDropdown"
        ></div>
        
        <!-- Dropdown -->
        <div 
            class="fixed z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xl max-h-64 w-80 user-mention-dropdown rounded-xl overflow-hidden"
            style="left: {{ $dropdownX }}px; top: {{ $dropdownY }}px;"
            tabindex="0"
            x-data
            x-init="
                $el.style.opacity = '0';
                $el.style.transform = 'scale(0.9) translateY(-8px)';
                $el.style.transition = 'all 0.15s cubic-bezier(0.4, 0, 0.2, 1)';
                setTimeout(() => {
                    $el.style.opacity = '1';
                    $el.style.transform = 'scale(1) translateY(0)';
                }, 10);
            "
            x-on:click.away="
                $el.style.transition = 'all 0.1s cubic-bezier(0.4, 0, 0.2, 1)';
                $el.style.opacity = '0';
                $el.style.transform = 'scale(0.95) translateY(-4px)';
                setTimeout(() => $wire.hideDropdown(), 100);
            "
        >
            <!-- Navigation Helper - Sticky to top -->
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-2 z-10 rounded-t-xl">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <div class="flex items-center space-x-4">
                        <span class="flex items-center space-x-1">
                            <span>↑</span>
                            <span>↓</span>
                        </span>
                        <span>{{ __('comments.mentions.dropdown.navigate') }}</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="flex items-center space-x-1">
                            <span>↵</span>
                            <span>{{ __('comments.mentions.dropdown.select') }}</span>
                        </span>
                        <span class="flex items-center space-x-1">
                            <span>Esc</span>
                            <span>{{ __('comments.mentions.dropdown.cancel') }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Scrollable user list -->
            <div class="overflow-y-auto max-h-48 p-2">
                @foreach($users as $index => $user)
                    <div 
                        class="flex items-center space-x-3 p-2 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 user-mention-item {{ $index === $selectedIndex ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                        wire:click="selectUser({{ $index }})"
                        wire:key="user-{{ $user['id'] }}"
                        x-on:mouseenter="$wire.selectedIndex = {{ $index }}"
                    >
                        <!-- User Avatar -->
                        <div class="flex-shrink-0 user-mention-avatar">
                            @if($user['avatar'])
                                <img 
                                    src="{{ Storage::url($user['avatar']) }}" 
                                    alt="{{ $user['username'] }}"
                                    class="w-8 h-8 rounded-full object-cover"
                                />
                            @else
                                <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                    {{ substr($user['username'] ?? 'U', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- User Info -->
                        <div class="flex-1 min-w-0 overflow-hidden">
                            <div class="flex items-center space-x-2 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate flex-shrink-0 user-mention-username">
                                    {{ $user['username'] }}
                                </p>
                                @if($user['name'] && $user['name'] !== $user['username'])
                                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate flex-shrink-0">
                                        ({{ $user['name'] }})
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1 user-mention-email">
                                {{ $user['email'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <style>
        /* Ensure consistent dropdown width and overflow handling */
        .user-mention-dropdown {
            width: 20rem !important; /* 320px - matches w-80 */
            min-width: 20rem !important;
            max-width: 20rem !important;
        }
        
        /* Consistent item heights */
        .user-mention-item {
            min-height: 3rem;
            display: flex;
            align-items: center;
        }
        
        /* Better text truncation for long usernames/emails */
        .user-mention-username {
            max-width: 8rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .user-mention-email {
            max-width: 12rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Ensure avatar and selection indicator don't shrink */
        .user-mention-avatar,
        .user-mention-indicator {
            flex-shrink: 0;
        }
    </style>
</div>
