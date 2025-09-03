<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\User;
use App\Observers\TaskObserver;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Filament\Notifications\Livewire\Notifications;
use Illuminate\Auth\Notifications\ResetPassword;
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
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'ms'])
                ->visible(
                    insidePanels: true,
                    outsidePanels: fn() => request()->is('admin/login')
                )
                ->outsidePanelPlacement(Placement::BottomCenter);
        });

        // Register script when Filament is serving (after auth & panel context resolved)
        \Filament\Facades\Filament::serving(function () {
            \Filament\Facades\Filament::registerRenderHook('panels::scripts.after', function () {
                return <<<'HTML'
                    <script>
                    // Adaptive Action Board badge polling (authenticated users only)
                    if(!window.__globalActionBoardBadge){
                        window.__globalActionBoardBadge = true;
                        let inFlight = false;
                        let intervalMs = 4000;            // Base interval (slower to reduce load)
                        const minInterval = 1000;         // Fastest after activity
                        const maxInterval = 12000;        // Slowest when idle
                        let idleCycles = 0;
                        function findLink(){
                            let link = document.querySelector('nav [href*="action-board"], [data-filament-navigation] [href*="action-board"]');
                            if(!link){
                                link = Array.from(document.querySelectorAll('nav a, [data-filament-navigation] a')).find(a=>/action\s*board/i.test(a.textContent||''));
                            }
                            return link;
                        }
                        // Ensure the badge is present
                        function ensureBadge(link){
                            if(!link) return null;
                            // Look for existing Filament native badge
                            let badge = link.querySelector('.fi-badge, [class*="fi-badge"]');
                            if(!badge){
                                // Create badge if it doesn't exist
                                const badgeContainer = link.querySelector('[class*="fi-sidebar-item-button"] span:last-child');
                                if(badgeContainer){
                                    badge = document.createElement('span');
                                    badge.className='fi-badge inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full shadow-sm';
                                    badge.style.minWidth='1.25rem';
                                    badge.style.minHeight='1.25rem';
                                    badgeContainer.appendChild(badge);
                                }
                            }
                            return badge;
                        }
                        // Refresh the badge
                        async function refresh(forceFast=false){
                            if(document.hidden) return; // Skip when tab not visible
                            if(inFlight) return; inFlight=true;
                            try{
                                // Fetch the count
                                const res = await fetch('/action-board/assigned-active-count',{headers:{'Accept':'application/json','Cache-Control':'no-cache'}});
                                if(!res.ok){
                                    // Backoff on auth / server errors
                                    intervalMs = Math.min(maxInterval, intervalMs * 1.5);
                                    return;
                                }
                                // Get the data
                                const data = await res.json();
                                // Get the count
                                const count = typeof data.count==='number'? data.count:0;
                                // Get the link
                                const link = findLink();
                                // Get the badge
                                const badge = ensureBadge(link);
                                if(!badge) return;
                                // Get the next count
                                const next = String(count);
                                // If the badge text content is not the same as the next count, update the badge
                                if(badge.textContent.trim()!==next){
                                    badge.textContent = next;
                                    badge.classList.add('animate-pulse');
                                    setTimeout(()=>badge.classList.remove('animate-pulse'),400);
                                    // Activity detected -> speed up briefly
                                    intervalMs = Math.max(minInterval, 1500);
                                    idleCycles = 0;
                                } else {
                                    // Increment the idle cycles
                                    idleCycles++;
                                    // Gradually slow down while idle
                                    if(idleCycles % 5 === 0){
                                        // Gradually slow down while idle
                                        intervalMs = Math.min(maxInterval, intervalMs + 1000);
                                    }
                                }
                                // Show/hide badge based on count
                                if(count === 0){
                                    badge.style.display = 'none';
                                } else {
                                    badge.style.display = 'flex';
                                }
                            }catch(e){
                                // Backoff on errors
                                intervalMs = Math.min(maxInterval, intervalMs * 1.5);
                            } finally { inFlight=false; }
                        }
                        // Schedule the loop
                        function scheduleLoop(){
                            refresh();
                            setTimeout(scheduleLoop, intervalMs);
                        }
                        // Listen for visibility changes
                        document.addEventListener('visibilitychange', ()=>{ if(!document.hidden){ intervalMs = Math.max(minInterval, 2000); refresh(); }});
                        // Listen for task events
                        ['task-created','task-updated','task-moved','task-status-updated'].forEach(ev=>document.addEventListener(ev, ()=>refresh(true)));
                        // Listen for Livewire events
                        ['livewire:load', 'livewire:update'].forEach(ev=>document.addEventListener(ev, ()=>setTimeout(()=>refresh(true), 500)));
                        // Listen for Kanban board events
                        ['kanban-order-updated', 'task-moved'].forEach(ev=>document.addEventListener(ev, ()=>setTimeout(()=>refresh(true), 300)));
                        // More aggressive polling when on Action Board page
                        setInterval(()=>{
                            if(window.location.pathname.includes('action-board')){
                                refresh(true);
                            }
                        }, 2000);
                        // Force a refresh
                        window.forceActionBoardBadgeRefresh = ()=>refresh(true);
                        // Listen for DOMContentLoaded
                        document.addEventListener('DOMContentLoaded', scheduleLoop);
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
    }
}
