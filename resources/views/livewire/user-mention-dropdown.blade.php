<div>
    @if($showDropdown && count($users) > 0)
        <!-- Backdrop for click outside -->
        <div 
            class="fixed inset-0 z-40"
            wire:click="hideDropdown"
        ></div>
        
        <!-- Dropdown -->
        <div 
            class="fixed z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl max-h-64 overflow-y-auto min-w-64"
            style="left: {{ $dropdownX }}px; top: {{ $dropdownY }}px;"
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
            <div class="p-2">
                @foreach($users as $index => $user)
                    <div 
                        class="flex items-center space-x-3 p-2 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-150 {{ $index === $selectedIndex ? 'bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700 ring-2 ring-primary-200 dark:ring-primary-700' : '' }}"
                        wire:click="selectUser({{ $index }})"
                        wire:key="user-{{ $user['id'] }}"
                        x-on:mouseenter="$wire.selectedIndex = {{ $index }}"
                    >
                        <!-- User Avatar -->
                        <div class="flex-shrink-0">
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
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $user['username'] }}
                                </p>
                                @if($user['name'] && $user['name'] !== $user['username'])
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ $user['name'] }})
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $user['email'] }}
                            </p>
                        </div>
                        
                        <!-- Selection Indicator -->
                        @if($index === $selectedIndex)
                            <div class="flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
