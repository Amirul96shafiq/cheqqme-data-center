<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\TaskObserver;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
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

        // Configure notification positioning to bottom right
        Notifications::alignment(Alignment::End);
        Notifications::verticalAlignment(VerticalAlignment::End);

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
                            let badge = link.querySelector('.ab-dynamic-badge');
                            if(!badge){
                                badge = document.createElement('span');
                                badge.className='ab-dynamic-badge inline-flex items-center justify-center rounded-full ml-2 px-2.5 py-0.5 text-xs font-medium bg-primary-500/10 text-primary-500 shadow-sm border border-primary-500 transition duration-200';
                                badge.style.minWidth='1.5rem';
                                link.appendChild(badge);
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
                                    badge.classList.add('ab-badge-pulse');
                                    setTimeout(()=>badge.classList.remove('ab-badge-pulse'),400);
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
                                // If the count is 0, update the badge to the gray color
                                if(count === 0){
                                    badge.classList.remove('bg-primary-100','text-primary-700','border-primary-500');
                                    badge.classList.add('bg-gray-200','text-gray-600','border','border-gray-300');
                                } else {
                                    // If the count is not 0, update the badge to the primary color
                                    badge.classList.remove('bg-gray-200','text-gray-600','border-gray-300');
                                    badge.classList.add('bg-primary-100','text-primary-700','border','border-primary-500');
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
