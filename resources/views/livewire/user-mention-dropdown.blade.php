<!-- User Mention Dropdown -->
<div>
    <!-- Show dropdown if it exists -->
    @if($showDropdown)
        @if(count($users) > 0)
        <!-- Backdrop for click outside -->
        <div 
            class="fixed inset-0 z-40"
            wire:click="hideDropdown"
        ></div>
        
        <!-- Dropdown -->
        <div 
            class="fixed z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xl max-h-64 w-80 user-mention-dropdown rounded-xl overflow-hidden"
            style="left: {{ $dropdownX }}px; top: {{ $dropdownY }}px;"
            x-show="true"
            x-data="{
                selectedIndex: {{ $selectedIndex }},
                users: @js($users),
                targetInputId: '{{ $targetInputId }}',
                isSelecting: false,
                selectionLock: false,
                
                init() {
                    // Initialize client-side state
                    this.selectedIndex = {{ $selectedIndex }};
                    
                    // Set up global keyboard navigation - listen on document since editor maintains focus
                    this.keydownHandler = (e) => {
                        if (this.selectionLock) {
                            return; // Don't prevent default, just ignore
                        }
                        
                        // Only handle navigation keys when dropdown is visible
                        if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            this.navigateUp();
                        } else if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            this.navigateDown();
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            this.selectUser();
                        } else if (e.key === 'Escape') {
                            e.preventDefault();
                            this.hideDropdown();
                        }
                        // All other keys (letters, numbers, etc.) are ignored here
                        // This allows them to pass through to the editor
                    };
                    
                    // Add global listener
                    document.addEventListener('keydown', this.keydownHandler);
                },
                
                navigateUp() {
                    this.selectedIndex = this.selectedIndex > 0 ? this.selectedIndex - 1 : this.users.length - 1;
                    this.updateSelection();
                },
                
                navigateDown() {
                    this.selectedIndex = this.selectedIndex < this.users.length - 1 ? this.selectedIndex + 1 : 0;
                    this.updateSelection();
                },
                
                updateSelection() {
                    // Update visual selection immediately
                    const items = this.$el.querySelectorAll('.user-mention-item');
                    items.forEach((item, index) => {
                        if (index === this.selectedIndex) {
                            item.classList.add('bg-blue-50', 'dark:bg-blue-900/20');
                            // Scroll the selected item into view
                            item.scrollIntoView({ 
                                block: 'nearest', 
                                behavior: 'smooth',
                                inline: 'nearest'
                            });
                        } else {
                            item.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
                        }
                    });
                    
                    // Update Livewire state (non-blocking)
                    $wire.updateSelectedIndex(this.selectedIndex);
                },
                
                selectUser() {
                    // Prevent multiple selections
                    if (this.selectionLock || this.isSelecting) {
                        console.log('🚫 Selection blocked - already selecting');
                        return;
                    }
                    
                    if (this.users[this.selectedIndex]) {
                        const user = this.users[this.selectedIndex];
                        console.log('🎯 Alpine.js selectUser called:', { username: user.username, userId: user.id });
                        
                        // Lock selection to prevent duplicates
                        this.selectionLock = true;
                        this.isSelecting = true;
                        
                        // Hide dropdown INSTANTLY - no delays
                        this.hideDropdown();
                        
                        // Dispatch userSelected event directly to the parent component (no Livewire round-trip)
                        window.dispatchEvent(new CustomEvent('userSelected', {
                            detail: {
                                username: user.username,
                                userId: user.id,
                                inputId: this.targetInputId
                            }
                        }));
                        
                        // Reset lock after a short delay to allow for cleanup
                        setTimeout(() => {
                            this.selectionLock = false;
                            this.isSelecting = false;
                        }, 100);
                    }
                },
                
                selectUserByIndex(index) {
                    console.log('🎯 Alpine.js selectUserByIndex called:', { index });
                    
                    // Prevent multiple selections
                    if (this.selectionLock || this.isSelecting) {
                        console.log('🚫 Selection blocked - already selecting');
                        return;
                    }
                    
                    // Set the selected index first
                    this.selectedIndex = index;
                    // Then select the user
                    this.selectUser();
                },
                
                hideDropdown() {
                    console.log('🎯 Alpine.js hideDropdown called');
                    // Hide instantly - no delays
                    this.$el.classList.add('instant-hide');
                    
                    // Remove global keyboard listener
                    if (this.keydownHandler) {
                        document.removeEventListener('keydown', this.keydownHandler);
                    }
                    
                    // Clean up Livewire state asynchronously (non-blocking)
                    setTimeout(() => {
                        $wire.hideDropdown();
                    }, 10);
                }
            }"
            x-init="
                // Remove any instant-hide class from previous sessions
                $el.classList.remove('instant-hide');
                
                $el.style.opacity = '0';
                $el.style.transform = 'scale(0.9) translateY(-8px)';
                $el.style.transition = 'all 0.15s cubic-bezier(0.4, 0, 0.2, 1)';
                setTimeout(() => {
                    $el.style.opacity = '1';
                    $el.style.transform = 'scale(1) translateY(0)';
                }, 10);
            "
            x-on:click.away="
                $el.classList.add('instant-hide');
                if (keydownHandler) {
                    document.removeEventListener('keydown', keydownHandler);
                }
                setTimeout(() => $wire.hideDropdown(), 0);
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
            <div class="overflow-y-auto max-h-48 p-2" id="user-mention-list">
                @foreach($users as $index => $user)
                    <div 
                        class="flex items-center space-x-3 p-2 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 user-mention-item transition-colors duration-75 {{ $index === $selectedIndex ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                        wire:key="user-{{ $user['id'] }}"
                        x-on:mouseenter="selectedIndex = {{ $index }}; updateSelection()"
                        x-on:click="selectUserByIndex({{ $index }})"
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
                                @php
                                    $userModel = \App\Models\User::find($user['id']);
                                    $defaultAvatarUrl = $userModel ? (new \Filament\AvatarProviders\UiAvatarsProvider())->get($userModel) : null;
                                @endphp
                                @if($defaultAvatarUrl)
                                    <img
                                        src="{{ $defaultAvatarUrl }}"
                                        alt="{{ $user['username'] }}"
                                        class="w-8 h-8 rounded-full object-cover"
                                    />
                                @else
                                    <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                        {{ substr($user['username'] ?? 'U', 0, 1) }}
                                    </div>
                                @endif
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
        @else
            <!-- No users found -->
            <div class="overflow-y-auto max-h-48 p-2">
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                    No users found for "{{ $search }}"
                </div>
            </div>
        @endif
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
            transition: background-color 0.075s ease-in-out;
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
        
        /* Smooth scrolling for the user list */
        #user-mention-list {
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }
        
        #user-mention-list::-webkit-scrollbar {
            width: 6px;
        }
        
        #user-mention-list::-webkit-scrollbar-track {
            background: transparent;
        }
        
        #user-mention-list::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
        
        #user-mention-list::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.7);
        }
        
        /* Focus styles removed - dropdown doesn't need focus for typing */
        
        /* Instant hide class for zero-delay closing */
        .user-mention-dropdown.instant-hide {
            transition: none !important;
            opacity: 0 !important;
            transform: scale(0.95) translateY(-4px) !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
        
        /* Enhanced selection styles */
        .user-mention-item.bg-blue-50,
        .user-mention-item.dark\\:bg-blue-900\\/20 {
            position: relative;
        }
        
        .user-mention-item.bg-blue-50::before,
        .user-mention-item.dark\\:bg-blue-900\\/20::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: rgb(59, 130, 246);
            border-radius: 0 2px 2px 0;
        }
    </style>
</div>
