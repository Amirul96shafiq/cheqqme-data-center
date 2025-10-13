{{-- Auth Loading Transition Component --}}
<div id="auth-loading-overlay" 
     class="fixed inset-0 z-50 flex items-center justify-center transition-all duration-500 ease-in-out"
     x-data="{ 
         isLoading: true,
         init() {
             // Set initial logo based on current theme
             this.setInitialLogo();
             
             // Hide loading after page is fully loaded
             window.addEventListener('load', () => {
                 setTimeout(() => {
                     this.isLoading = false;
                     // Remove from DOM after animation completes
                     setTimeout(() => {
                         this.$el.remove();
                     }, 500);
                 }, 1500); // Show loading for at least 800ms for smooth UX
             });
         },
         setInitialLogo() {
             const logo = document.getElementById('authLoadingLogo');
             if (!logo) return;
             
             // Check current theme
             const isDark = document.documentElement.classList.contains('dark') || 
                           (!document.documentElement.classList.contains('light') && 
                            window.matchMedia('(prefers-color-scheme: dark)').matches);
             
             // Set appropriate logo
             logo.src = isDark ? 
                 `${window.location.origin}/logos/logo-dark.png` : 
                 `${window.location.origin}/logos/logo-light.png`;
         }
     }"
     x-show="isLoading"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-500"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    {{-- Background Overlay --}}
    <div class="absolute inset-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm"></div>
    
    {{-- Loading Content --}}
    <div class="relative z-10 flex flex-col items-center justify-center space-y-6">
        {{-- Logo --}}
        <div class="relative">
            <img id="authLoadingLogo"
                 src="{{ asset('logos/logo-dark.png') }}" 
                 alt="{{ config('app.name') }} Logo"
                 class="h-20 w-auto opacity-0 animate-fade-in-up"
                 style="animation-delay: 0.2s; animation-fill-mode: forwards;">
        </div>
        
        {{-- Loading Spinner --}}
        <div class="relative opacity-0 animate-fade-in"
             style="animation-delay: 0.4s; animation-fill-mode: forwards;">
            <x-icons.custom-icon name="refresh" class="w-12 h-12" color="text-primary-500" />
        </div>
        
        {{-- Loading Text --}}
        <div class="text-center space-y-2">
            <p class="text-lg font-medium text-gray-700 dark:text-gray-300 opacity-0 animate-fade-in-up"
               style="animation-delay: 0.6s; animation-fill-mode: forwards;">
                {{ __('auth.loading') }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 opacity-0 animate-fade-in-up"
               style="animation-delay: 0.8s; animation-fill-mode: forwards;">
                {{ __('auth.please_wait') }}
            </p>
        </div>
    </div>
</div>

{{-- Loading Animation Styles --}}
<style>
    @keyframes fade-in {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
    
    .animate-fade-in {
        animation: fade-in 0.6s ease-out;
    }
    
    .animate-fade-in-up {
        animation: fade-in-up 0.8s ease-out;
    }
    
    /* Ensure loading overlay is above everything */
    #auth-loading-overlay {
        z-index: 9999;
    }
    
    /* Dark mode adjustments for better contrast */
    .dark #auth-loading-overlay .absolute.inset-0 {
        background: rgba(17, 24, 39, 0.95);
        backdrop-filter: blur(12px);
    }
    
    /* Light mode adjustments */
    #auth-loading-overlay .absolute.inset-0 {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
    }
</style>
