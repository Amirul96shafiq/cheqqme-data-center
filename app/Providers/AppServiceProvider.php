<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'ms'])
                ->visible(
                    insidePanels: true,
                    outsidePanels: fn() => request()->is('admin/login')
                )
                ->outsidePanelPlacement(Placement::BottomCenter);
        });

        \Filament\Facades\Filament::registerRenderHook('panels::scripts.after', function () {
            return <<<'HTML'
                <script>
                // Global Action Board badge
                if(!window.__globalActionBoardBadge){
                    window.__globalActionBoardBadge = true;
                    let inFlight = false;
                    function findLink(){
                        let link = document.querySelector('nav [href*="action-board"], [data-filament-navigation] [href*="action-board"]');
                        if(!link){
                            link = Array.from(document.querySelectorAll('nav a, [data-filament-navigation] a')).find(a=>/action\s*board/i.test(a.textContent||''));
                        }
                        return link;
                    }
                    function ensureBadge(link){
                        if(!link) return null;
                        let badge = link.querySelector('.ab-dynamic-badge');
                        if(!badge){
                            badge = document.createElement('span');
                            badge.className='ab-dynamic-badge';
                            // Approximate Filament native badge styling
                            badge.classList.add(
                                'inline-flex',
                                'items-center',
                                'justify-center',
                                'rounded-full',
                                'ml-2',
                                'px-2.5',
                                'py-0.5',
                                'text-xs',
                                'font-medium',
                                'bg-primary-500/10',
                                'text-primary-500',
                                'shadow-sm',
                                'border',
                                'border-primary-500',
                                'transition',
                                'duration-200'
                            );
                            // Min width to keep pill shape consistent on single digits
                            badge.style.minWidth='1.5rem';
                            link.appendChild(badge);
                        }
                        return badge;
                    }
                    async function refresh(){
                        if(inFlight) return; inFlight=true;
                        try{
                            const res = await fetch('/action-board/assigned-active-count',{headers:{'Accept':'application/json','Cache-Control':'no-cache'}});
                            if(!res.ok) return;
                            const data = await res.json();
                            const count = typeof data.count==='number'? data.count:0;
                            const link = findLink();
                            const badge = ensureBadge(link);
                            if(!badge) return;
                            const next = String(count);
                            if(badge.textContent.trim()!==next){
                                badge.textContent = next;
                                badge.classList.add('ab-badge-pulse');
                                setTimeout(()=>badge.classList.remove('ab-badge-pulse'),400);
                            }
                            // Color handling via Tailwind classes
                            if(count === 0){
                                badge.classList.remove('bg-primary-100','text-primary-700','border-primary-500');
                                badge.classList.add('bg-gray-200','text-gray-600','border','border-gray-300');
                            } else {
                                badge.classList.remove('bg-gray-200','text-gray-600','border-gray-300');
                                badge.classList.add('bg-primary-100','text-primary-700','border','border-primary-500');
                            }
                        }catch(e){} finally { inFlight=false; }
                    }
                    // Initial and periodic
                    document.addEventListener('DOMContentLoaded', refresh);
                    setInterval(()=>{ if(!document.hidden) refresh(); }, 1200);
                    document.addEventListener('visibilitychange', ()=>{ if(!document.hidden) refresh(); });
                    ['task-created','task-updated','task-moved','task-status-updated'].forEach(ev=>document.addEventListener(ev, refresh));
                    window.forceActionBoardBadgeRefresh = refresh;
                }
                </script>
                                <script>
                                // Global auto reload on task deletion (single installer)
                                if(!window.__ffGlobalDeleteReload){
                                    window.__ffGlobalDeleteReload = true;
                                    const reload = ()=>{ if(window.__ffReloadScheduled) return; window.__ffReloadScheduled=true; setTimeout(()=>location.reload(), 300); };
                                    window.addEventListener('kanban-task-deleted', reload);
                                    document.addEventListener('kanban-task-deleted', reload);
                                    // Also watch notifications
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

        // Late <style> tag so it loads after any on-request asset
        \Filament\Facades\Filament::registerRenderHook('panels::head.end', function () {
            return '<style>.ff-card__title{font-weight:400!important}</style>';
        });
    }
}
