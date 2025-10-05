<!-- Pure Alpine.js User Mention Dropdown -->
<div 
    data-mention-dropdown-root
    x-data="userMentionDropdown()"
    x-cloak
    x-show="showDropdown"
    x-transition.opacity.duration.0ms
    class="fixed z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xl max-h-64 w-80 user-mention-dropdown rounded-xl overflow-hidden origin-top-left transform-gpu will-change-transform motion-reduce:transition-none motion-reduce:transform-none"
    :style="dropdownStyle"
    @click.away="hideDropdown()"
    @keydown.escape="hideDropdown()"
    @mousedown.stop
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
		<template x-if="users.length === 0">
			<div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
				<template x-if="errorMessage">
					<span class="flex flex-col items-center justify-center">
						<span class="mb-4 inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
							<x-heroicon-o-exclamation-triangle class="w-5 h-5" />
						</span>
						<span x-text="errorMessage"></span>
					</span>
				</template>
				<template x-if="!errorMessage">
					<span class="flex flex-col items-center justify-center">
						<span class="mb-4 inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300">
							<x-heroicon-o-magnifying-glass class="w-5 h-5" />
						</span>
						<span x-text="search ? ('{{ __('comments.mentions.no_users_found_for') }} ' + (search.startsWith('@') ? search : '@' + search)) : '{{ __('comments.mentions.loading') }}'"></span>
					</span>
				</template>
			</div>
		</template>
        
        <template x-for="(user, index) in users" :key="user.id">
            <div 
                class="flex items-center space-x-3 p-2 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 user-mention-item transition-colors duration-75"
                :class="{
                    'bg-primary-50 dark:bg-primary-900/20 text-primary-500': selectedIndex === index,
                    '': user.is_special
                }"
                :data-index="index"
                @mouseenter="selectedIndex = index"
                @mousedown.prevent
                @click.stop="selectUser(index)"
            >
                <!-- User Avatar -->
                <div class="flex-shrink-0 user-mention-avatar">
                    <template x-if="user.is_special">
                        <!-- Special @Everyone avatar -->
                        <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                        </div>
                    </template>
                    
                    <template x-if="!user.is_special && user.avatar">
                        <img
                            :src="user.avatar_url"
                            :alt="user.username"
                            class="w-8 h-8 rounded-full object-cover"
                        />
                    </template>
                    
                    <template x-if="!user.is_special && !user.avatar && user.default_avatar">
                        <img
                            :src="user.default_avatar"
                            :alt="user.username"
                            class="w-8 h-8 rounded-full object-cover"
                        />
                    </template>
                    
                    <template x-if="!user.is_special && !user.avatar && !user.default_avatar">
                        <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                            <span x-text="user.username.charAt(0).toUpperCase()"></span>
                        </div>
                    </template>
                </div>
                
                <!-- User Info -->
                <div class="flex-1 min-w-0 overflow-hidden">
                    <div class="flex items-center space-x-2 min-w-0">
                        <p 
                            class="text-sm font-medium truncate flex-shrink-0 user-mention-username"
                            :class="user.is_special ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-gray-100'"
                            x-text="user.username"
                        ></p>
                        <template x-if="user.name && user.name !== user.username">
                            <span class="text-xs text-gray-500 dark:text-gray-400 truncate flex-shrink-0">
                                (<span x-text="user.name"></span>)
                            </span>
                        </template>
                    </div>
                    <p 
                        class="text-xs truncate mt-1 user-mention-email"
                        :class="user.is_special ? 'text-primary-500 dark:text-primary-300' : 'text-gray-500 dark:text-gray-400'"
                        x-text="user.email"
                    ></p>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function userMentionDropdown() {
    return {
        showDropdown: false,
        users: [],
        selectedIndex: 0,
        search: '',
        errorMessage: '',
        extraAtActive: false,
        targetInputId: '',
        dropdownX: 0,
        dropdownY: 0,
        selectionLock: false,
        allUsers: [], // Cache for all users
        keydownHandler: null,
        
        init() {
            // Load all users once on initialization
            this.loadAllUsers();
            
            // Listen for global events
            window.addEventListener('showMentionDropdown', (e) => {
                this.showDropdown = true;
                this.search = e.detail.searchTerm || '';
                this.targetInputId = e.detail.inputId || '';
                this.dropdownX = e.detail.x || 0;
                this.dropdownY = e.detail.y || 0;
                this.selectedIndex = 0;
                if (e.detail.hasExtraAt) {
                    this.extraAtActive = true;
                    this.errorMessage = "{{ __('comments.mentions.@_not_allowed') }}";
                    this.users = [];
                } else {
                    // Reset any previous error and show results (even for single '@')
                    this.extraAtActive = false;
                    this.errorMessage = '';
                    this.searchUsers();
                }
                this.setupKeyboardNavigation();
            });
            
            window.addEventListener('hideMentionDropdown', () => {
                this.hideDropdown();
            });

            // Extra safety: close dropdown when a selection is announced globally
            window.addEventListener('userSelected', () => {
                this.hideDropdown();
            });
        },
        
        async loadAllUsers() {
            try {
                const response = await fetch('/api/users/mention-search');
                if (response.ok) {
                    const data = await response.json();
                    this.allUsers = data.users || [];
                }
            } catch (error) {
                console.error('Failed to load users for mentions:', error);
                this.allUsers = [];
            }
        },
        
        searchUsers() {
            const rawSearch = (this.search || '');
            const afterFirstAt = rawSearch.replace(/^@/, '');

            // Disallow additional '@' characters beyond the first trigger
            if (this.extraAtActive || afterFirstAt.includes('@')) {
                this.errorMessage = "{{ __('comments.mentions.@_not_allowed') }}";
                this.users = [];
                return;
            }

            this.errorMessage = '';
            const cleanSearch = afterFirstAt.toLowerCase();
            
            // Get already mentioned users from the current comment text
            const alreadyMentionedUsers = this.getAlreadyMentionedUsers();
            
            
            // Start with @Everyone if search matches and not already mentioned
            const users = [];
            if ((cleanSearch === '' || 'everyone'.includes(cleanSearch)) && !alreadyMentionedUsers.includes('@Everyone')) {
                users.push({
                    id: '@Everyone',
                    username: 'Everyone',
                    email: '{{ __("comments.mentions.dropdown.notify_all") }}',
                    name: 'Everyone',
                    avatar: null,
                    is_special: true
                });
            }
            
            // Filter regular users (exclude already mentioned ones)
            const filteredUsers = this.allUsers.filter(user => {
                const username = (user.username || '').toLowerCase();
                const email = (user.email || '').toLowerCase();
                const name = (user.name || '').toLowerCase();
                
                // Check if user matches search criteria
                const matchesSearch = username.includes(cleanSearch) || 
                                    email.includes(cleanSearch) || 
                                    name.includes(cleanSearch);
                
                // Check if user is not already mentioned
                // Use exact matching only to prevent false positives
                const userIdentifiers = [
                    user.username,
                    user.name
                ].filter(Boolean); // Remove empty values
                
                const isAlreadyMentioned = userIdentifiers.some(identifier => 
                    alreadyMentionedUsers.some(mentioned => 
                        // Exact match only - no partial matching
                        mentioned.toLowerCase() === identifier.toLowerCase()
                    )
                );
                
                return matchesSearch && !isAlreadyMentioned;
            });
            
            // Limit to 10 results and add avatar URLs
            const regularUsers = filteredUsers.slice(0, 10).map(user => ({
                ...user,
                avatar_url: user.avatar ? `/storage/${user.avatar}` : null,
                // Prefer server-provided default avatar (Filament provider) and fall back to UI Avatars
                default_avatar: user.default_avatar || this.generateDefaultAvatar(user),
                is_special: false
            }));
            
            this.users = [...users, ...regularUsers];
        },
        
        generateDefaultAvatar(user) {
            const name = user.name || user.username || 'U';
            return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=3b82f6&color=ffffff&size=64&format=png`;
        },
        
        getAlreadyMentionedUsers() {
            const mentionedUsers = [];
            
            // Find the target editor/input
            let targetEditor = null;
            
            // First try to find by targetInputId
            if (this.targetInputId) {
                targetEditor = document.querySelector(`#${this.targetInputId}`) ||
                              document.querySelector(`[data-input-id="${this.targetInputId}"]`);
            }
            
            // If not found, try to find the active/composer editor
            if (!targetEditor) {
                // Look for composer trix-editor first
                targetEditor = document.querySelector('[data-composer] trix-editor') ||
                              document.querySelector('trix-editor') ||
                              document.querySelector(".ProseMirror") ||
                              document.querySelector('[contenteditable="true"]');
            }
            
            
            if (!targetEditor) {
                return mentionedUsers;
            }
            
            let text = '';
            
            // Get text content based on editor type
            if (targetEditor.tagName === 'TRIX-EDITOR') {
                // For Trix editor, try multiple methods to get the actual content
                if (targetEditor.editor && targetEditor.editor.getDocument) {
                    text = targetEditor.editor.getDocument().toString();
                } else if (targetEditor.editor && targetEditor.editor.getHTML) {
                    text = targetEditor.editor.getHTML();
                } else {
                    text = targetEditor.textContent || targetEditor.innerText || '';
                }
                
                // If we got placeholder text, try to get the actual HTML content
                if (text.includes('voluptatem consequatur') || text.includes('Lorem ipsum')) {
                    text = targetEditor.innerHTML || targetEditor.textContent || '';
                }
            } else if (targetEditor.classList.contains('ProseMirror')) {
                // For ProseMirror editor
                text = targetEditor.textContent || targetEditor.innerText || '';
            } else {
                // For contenteditable elements
                text = targetEditor.textContent || targetEditor.innerText || '';
            }
            
            // Extract mentions using regex similar to the backend logic
            // Remove HTML tags for plain text processing
            text = text.replace(/<[^>]*>/g, '');
            
            
            // Get the current search term (what user is typing after @)
            const currentSearch = this.search.replace(/^@/, '').trim();
            
            // Check for @Everyone mention
            if (text.includes('@Everyone')) {
                mentionedUsers.push('@Everyone');
            }
            
            // Find all @mentions, but exclude the one currently being typed
            const mentionMatches = text.match(/@([A-Za-z0-9_.-]+(?:\s+[A-Za-z0-9_.-]+){0,4})/g);
            
            if (mentionMatches) {
                mentionMatches.forEach(mention => {
                    // Remove the @ symbol and get the username/name part
                    const cleanMention = mention.substring(1).trim();
                    
                    // Only include if it's not the current search term being typed
                    if (cleanMention && cleanMention !== currentSearch) {
                        // Only add the exact mention, not individual words
                        // This prevents partial matching issues (e.g., "Amirul" matching "Amirul96Shafiq")
                        mentionedUsers.push(cleanMention);
                    }
                });
            }
            
            return mentionedUsers;
        },
        
        setupKeyboardNavigation() {
            // Remove existing handler
            if (this.keydownHandler) {
                document.removeEventListener('keydown', this.keydownHandler);
            }
            
            this.keydownHandler = (e) => {
                if (this.selectionLock || !this.showDropdown) {
                    return;
                }
                
                switch (e.key) {
                    case 'ArrowUp':
                        e.preventDefault();
                        this.navigateUp();
                        break;
                    case 'ArrowDown':
                        e.preventDefault();
                        this.navigateDown();
                        break;
                    case 'Enter':
                        e.preventDefault();
                        this.selectUser(this.selectedIndex);
                        break;
                    case 'Escape':
                        e.preventDefault();
                        this.hideDropdown();
                        break;
                }
            };
            
            document.addEventListener('keydown', this.keydownHandler);
        },
        
        navigateUp() {
            this.selectedIndex = this.selectedIndex > 0 ? this.selectedIndex - 1 : this.users.length - 1;
            this.scrollToSelected();
        },
        
        navigateDown() {
            this.selectedIndex = this.selectedIndex < this.users.length - 1 ? this.selectedIndex + 1 : 0;
            this.scrollToSelected();
        },
        
        scrollToSelected() {
            this.$nextTick(() => {
                const selectedItem = this.$el.querySelector(`[data-index="${this.selectedIndex}"]`);
                if (selectedItem) {
                    selectedItem.scrollIntoView({ 
                        block: 'nearest', 
                        behavior: 'smooth' 
                    });
                }
            });
        },
        
        selectUser(index) {
            if (this.selectionLock || !this.users[index]) {
                return;
            }
            
            this.selectionLock = true;
            const user = this.users[index];
            
            // Hide dropdown immediately
            this.hideDropdown();
            
            // Dispatch selection event
            window.dispatchEvent(new CustomEvent('userSelected', {
                detail: {
                    username: user.username,
                    userId: user.id,
                    inputId: this.targetInputId
                }
            }));
            
            // Reset lock after delay
            setTimeout(() => {
                this.selectionLock = false;
            }, 300);
        },
        
        hideDropdown() {
            this.showDropdown = false;
            this.users = [];
            this.search = '';
            this.selectedIndex = 0;
            
            // Remove keyboard handler
            if (this.keydownHandler) {
                document.removeEventListener('keydown', this.keydownHandler);
                this.keydownHandler = null;
            }
        },
        
        get dropdownStyle() {
            return {
                left: `${this.dropdownX}px`,
                top: `${this.dropdownY}px`,
                transform: 'none',
            };
        },
        
        destroy() {
            if (this.keydownHandler) {
                document.removeEventListener('keydown', this.keydownHandler);
            }
        }
    }
}
</script>

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
    scrollbar-color: rgb(161 161 170) transparent;
}

#user-mention-list::-webkit-scrollbar {
    width: 6px;
}

#user-mention-list::-webkit-scrollbar-track {
    background: transparent;
}

#user-mention-list::-webkit-scrollbar-thumb {
    background-color: rgb(161 161 170);
    border-radius: 3px;
}

#user-mention-list::-webkit-scrollbar-thumb:hover {
    background-color: rgb(113 113 122);
}

/* Remove scrollbar buttons (arrows) */
#user-mention-list::-webkit-scrollbar-button {
    display: none;
}

#user-mention-list::-webkit-scrollbar-corner {
    display: none;
}

/* Dark mode scrollbar styling */
.dark #user-mention-list {
    scrollbar-color: rgb(113 113 122) transparent;
}

.dark #user-mention-list::-webkit-scrollbar-thumb {
    background-color: rgb(113 113 122);
}

.dark #user-mention-list::-webkit-scrollbar-thumb:hover {
    background-color: rgb(82 82 91);
}

/* Enhanced selection styles */
.user-mention-item.bg-primary-50,
.user-mention-item.dark\:bg-primary-900\/20 {
    position: relative;
}
</style>
