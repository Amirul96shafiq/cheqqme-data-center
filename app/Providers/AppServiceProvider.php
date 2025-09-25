<?php

namespace App\Providers;

use App\Helpers\GitHelper;
use App\Models\Task;
use App\Models\User;
use App\Observers\TaskObserver;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Filament\Notifications\Livewire\Notifications;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Trigger the database notifications trigger
        DatabaseNotifications::trigger('filament.notifications.database-notifications-trigger');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Microsoft socialite provider
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('microsoft', \SocialiteProviders\Microsoft\Provider::class);
        });

        // Register Task observer to ensure activity logging on order/status changes
        Task::observe(TaskObserver::class);

        // Customize password reset URLs to include email parameter
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return url(route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false));
        });

        // Register custom KanbanBoard Livewire component alias
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('relaticle.flowforge.kanban-board', \App\Http\Livewire\Relaticle\Flowforge\KanbanBoard::class);
        }

        // Register custom language switch
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'ms'])
                ->visible(
                    insidePanels: true,
                    outsidePanels: fn () => request()->is('admin/login')
                )
                ->outsidePanelPlacement(Placement::BottomCenter);
        });

        // Register script when Filament is serving (after auth & panel context resolved)
        \Filament\Facades\Filament::serving(function () {
            \Filament\Facades\Filament::registerRenderHook('panels::scripts.after', function () {
                return <<<'HTML'
                    <script>
                    // Action Board badge polling (authenticated users only)
                    if(!window.__globalActionBoardBadge){
                        window.__globalActionBoardBadge = true;
                        
                        let pollingInterval = null;
                        let inFlight = false;
                        
                        function findLink(){
                            let link = document.querySelector('nav [href*="action-board"], [data-filament-navigation] [href*="action-board"]');
                            if(!link){
                                link = Array.from(document.querySelectorAll('nav a, [data-filament-navigation] a')).find(a=>/action\s*board/i.test(a.textContent||''));
                            }
                            return link;
                        }
                        
                        // Ensure the badge is present - works for both collapsed and expanded sidebar
                        function ensureBadge(link){
                            if(!link) return null;
                            
                            // Look for existing custom badge first
                            let badge = link.querySelector('.action-board-badge');
                            
                            if(!badge){
                                // Create badge positioned relative to the icon container
                                const iconContainer = link.querySelector('.fi-sidebar-item-icon').parentElement;
                                if(iconContainer){
                                    badge = document.createElement('span');
                                    badge.className='action-board-badge absolute bottom-2 right-4 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full shadow-lg z-10 bounce-bounce-s';
                                    badge.style.minWidth='1.25rem';
                                    badge.style.minHeight='1.25rem';
                                    badge.style.fontSize='0.75rem';
                                    badge.style.lineHeight='1';
                                    
                                    // Ensure the icon container is positioned relatively
                                    iconContainer.style.position = 'relative';
                                    iconContainer.appendChild(badge);
                                }
                            }
                            return badge;
                        }
                        
                        // Update badge with new count
                        function updateBadge(count){
                            const link = findLink();
                            const badge = ensureBadge(link);
                            if(!badge) return;
                            
                            const next = String(count);
                            if(badge.textContent.trim() !== next){
                                badge.textContent = next;
                                badge.classList.add('animate-pulse');
                                setTimeout(()=>badge.classList.remove('animate-pulse'), 600);
                                console.log('Badge updated:', count);
                            }
                            
                            // Show/hide badge based on count - always visible when count > 0
                            if(count === 0){
                                badge.style.display = 'none';
                            } else {
                                badge.style.display = 'inline-flex';
                                // Ensure badge is always visible regardless of sidebar state
                                badge.style.visibility = 'visible';
                                badge.style.opacity = '1';
                            }
                        }
                        
                        // Poll for updates
                        async function pollForUpdates(){
                            if(document.hidden || inFlight) return;
                            inFlight = true;
                            
                            try{
                                const res = await fetch('/action-board/assigned-active-count', {
                                    headers: {'Accept': 'application/json', 'Cache-Control': 'no-cache'},
                                    credentials: 'same-origin'
                                });
                                
                                if(res.ok){
                                    const data = await res.json();
                                    updateBadge(data.count);
                                } else {
                                    console.warn('Polling failed:', res.status);
                                }
                            }catch(e){
                                console.error('Polling error:', e);
                            } finally { 
                                inFlight = false;
                            }
                        }
                        
                        // Start polling
                        function startPolling(){
                            console.log('Starting badge polling...');
                            
                            // Clear any existing interval
                            if(pollingInterval) {
                                clearInterval(pollingInterval);
                            }
                            
                            // Initial poll
                            pollForUpdates();
                            
                            // Set up polling interval (every 3 seconds)
                            pollingInterval = setInterval(pollForUpdates, 3000);
                            
                            // Listen for task events to trigger immediate refresh
                            ['task-created','task-updated','task-moved','task-status-updated'].forEach(ev=>{
                                document.addEventListener(ev, ()=>{
                                    console.log('Task event detected:', ev);
                                    pollForUpdates();
                                });
                            });
                            
                            // Listen for Livewire events
                            ['livewire:load', 'livewire:update'].forEach(ev=>{
                                document.addEventListener(ev, ()=>{
                                    console.log('Livewire event detected:', ev);
                                    setTimeout(()=>pollForUpdates(), 500);
                                });
                            });
                            
                            // Listen for Kanban board events
                            ['kanban-order-updated', 'task-moved'].forEach(ev=>{
                                document.addEventListener(ev, ()=>{
                                    console.log('Kanban event detected:', ev);
                                    setTimeout(()=>pollForUpdates(), 300);
                                });
                            });
                        }
                        
                        // Handle sidebar state changes
                        function handleSidebarToggle() {
                            // Re-ensure badge is properly positioned after sidebar toggle
                            setTimeout(() => {
                                const link = findLink();
                                if(link) {
                                    const badge = link.querySelector('.action-board-badge');
                                    if(badge) {
                                        // Force badge repositioning
                                        const iconContainer = link.querySelector('.fi-sidebar-item-icon').parentElement;
                                        if(iconContainer && badge.parentElement !== iconContainer) {
                                            iconContainer.appendChild(badge);
                                        }
                                    }
                                }
                            }, 100);
                        }
                        
                        // Initialize when DOM is ready
                        document.addEventListener('DOMContentLoaded', function() {
                            startPolling();
                            
                            // Force refresh function for manual updates
                            window.forceActionBoardBadgeRefresh = function() {
                                console.log('Manual badge refresh triggered');
                                pollForUpdates();
                            };
                            
                            // Listen for sidebar toggle events
                            document.addEventListener('click', function(e) {
                                // Check if sidebar toggle button was clicked
                                if(e.target.closest('[data-filament-sidebar-toggle]') || 
                                   e.target.closest('.fi-sidebar-toggle') ||
                                   e.target.closest('[x-on\\:click*="sidebar.toggle"]')) {
                                    handleSidebarToggle();
                                }
                            });
                            
                            // Also listen for Alpine.js store changes
                            if(window.Alpine && window.Alpine.store) {
                                try {
                                    const sidebarStore = window.Alpine.store('sidebar');
                                    if(sidebarStore) {
                                        // Override the toggle method to detect changes
                                        const originalToggle = sidebarStore.toggle;
                                        sidebarStore.toggle = function() {
                                            const result = originalToggle.call(this);
                                            setTimeout(handleSidebarToggle, 50);
                                            return result;
                                        };
                                    }
                                } catch(e) {
                                    console.log('Could not hook into sidebar store:', e);
                                }
                            }
                        });
                    }
                    </script>
                    <script>
                    // Global auto reload on task deletion (still only for authenticated pages)
                    if(!window.__ffGlobalDeleteReload){
                        window.__ffGlobalDeleteReload = true;
                        const reload = ()=>{ if(window.__ffReloadScheduled) return; window.__ffReloadScheduled=true; setTimeout(()=>location.reload(), 300); };
                        window.addEventListener('kanban-task-deleted', reload);
                        document.addEventListener('kanban-task-deleted', reload);
                        const mo = new MutationObserver(()=>{
                            const hit = Array.from(document.querySelectorAll('[role="alert"],[data-notification]'))
                                .some(el=>/Task deleted/i.test(el.textContent||''));
                            if(hit) reload();
                        });
                        try{ mo.observe(document.body,{childList:true,subtree:true}); }catch(e){}
                    }
                    </script>
                HTML;
            });
        });

        // Late <style> tag so it loads after any on-request asset
        \Filament\Facades\Filament::registerRenderHook('panels::head.end', function () {
            return '<style>.ff-card__title{font-weight:400!important}</style>';
        });

        // Share git version with views
        View::composer([
            'components.auth-hero',
        ], function ($view) {
            $view->with('gitVersion', GitHelper::getVersionString());
        });
    }
}
