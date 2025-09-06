<script>
function openGreetingModal() {
    // Create modal overlay
    const modal = document.createElement('div');
    modal.id = 'greeting-modal-overlay';
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    modal.style.backdropFilter = 'blur(12px)';
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-100 opacity-100 border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('greetingmodal.modal-title') }}
                    </h3>
                    <button 
                        onclick="closeGreetingModal()" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                        aria-label="Close"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    {{ __('greetingmodal.modal-content') }}
                </p>
                <div class="flex justify-end">
                    <button 
                        onclick="closeGreetingModal()" 
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors"
                    >
                        {{ __('greetingmodal.modal-close') }}
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(modal);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
    
    // Close on backdrop click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeGreetingModal();
        }
    });
    
    // Close on escape key
    const handleEscape = function(e) {
        if (e.key === 'Escape') {
            closeGreetingModal();
        }
    };
    document.addEventListener('keydown', handleEscape);
    
    // Store the escape handler for cleanup
    modal.escapeHandler = handleEscape;
}

function closeGreetingModal() {
    const modal = document.getElementById('greeting-modal-overlay');
    if (modal) {
        // Remove escape key listener
        if (modal.escapeHandler) {
            document.removeEventListener('keydown', modal.escapeHandler);
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        // Remove modal
        modal.remove();
    }
}

// Auto-detect clicks on greeting menu item
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Filament to load
    setTimeout(function() {
        console.log('Looking for greeting menu item...');
        
        // Try multiple selectors to find the greeting menu item
        const selectors = [
            '[data-filament-dropdown-list] a[href="javascript:void(0)"]',
            '.fi-dropdown-list a[href="javascript:void(0)"]',
            '[role="menu"] a[href="javascript:void(0)"]',
            '[data-filament-dropdown-list-item] a[href="javascript:void(0)"]',
            '.fi-dropdown-list-item a[href="javascript:void(0)"]',
            'a[href="javascript:void(0)"]'
        ];
        
        let greetingLink = null;
        
        for (const selector of selectors) {
            const links = document.querySelectorAll(selector);
            console.log(`Found ${links.length} links with selector: ${selector}`);
            
            links.forEach(function(link) {
                console.log('Checking link:', link.textContent.trim(), link.href);
                // Check if this is our greeting menu item by looking for time-based greetings
                const text = link.textContent.trim().toLowerCase();
                // More specific check - must be in user menu dropdown
                if ((text.includes('good morning') || text.includes('good afternoon') || 
                    text.includes('good evening') || text.includes('goodnight') ||
                    text.includes('morning') || text.includes('afternoon') || 
                    text.includes('evening') || text.includes('night')) &&
                    (link.closest('[data-filament-dropdown-list]') || 
                     link.closest('.fi-dropdown-list') ||
                     link.closest('[role="menu"]'))) {
                    greetingLink = link;
                    console.log('Found greeting menu item:', text);
                }
            });
            
            if (greetingLink) break;
        }
        
        if (greetingLink) {
            console.log('Attaching click handler to greeting menu item');
            greetingLink.addEventListener('click', function(e) {
                console.log('Greeting menu item clicked!');
                e.preventDefault();
                e.stopPropagation();
                openGreetingModal();
            });
        } else {
            console.log('Greeting menu item not found');
        }
    }, 2000); // Increased timeout to ensure Filament is fully loaded
});

// Fallback: Use event delegation to catch clicks on any element
document.addEventListener('click', function(e) {
    // Check if clicked element or its parent contains greeting text
    let element = e.target;
    let foundGreeting = false;
    
    // Check up to 3 parent levels
    for (let i = 0; i < 3; i++) {
        if (element && element.textContent) {
            const text = element.textContent.trim().toLowerCase();
            // More specific check - must contain greeting AND be in user menu
            if ((text.includes('good morning') || text.includes('good afternoon') || 
                text.includes('good evening') || text.includes('goodnight') ||
                text.includes('morning') || text.includes('afternoon') || 
                text.includes('evening') || text.includes('night')) &&
                (element.closest('[data-filament-dropdown-list]') || 
                 element.closest('.fi-dropdown-list') ||
                 element.closest('[role="menu"]'))) {
                foundGreeting = true;
                break;
            }
        }
        element = element?.parentElement; // Safe navigation with optional chaining
    }
    
    if (foundGreeting) {
        console.log('Greeting text clicked via event delegation');
        e.preventDefault();
        e.stopPropagation();
        openGreetingModal();
    }
});

// Test function - you can call this manually in browser console: testGreetingModal()
window.testGreetingModal = function() {
    console.log('Testing greeting modal...');
    openGreetingModal();
};

// Make functions globally available
window.openGreetingModal = openGreetingModal;
window.closeGreetingModal = closeGreetingModal;
</script>
