<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('event.tabs.all')),
            'upcoming' => Tab::make(__('event.tabs.upcoming'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('start_datetime', '>', now()))
                ->badge(fn () => \App\Models\Event::where('start_datetime', '>', now())->count()),
            'today' => Tab::make(__('event.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('start_datetime', now()->toDateString()))
                ->badge(fn () => \App\Models\Event::whereDate('start_datetime', now()->toDateString())->count()),
            'this_week' => Tab::make(__('event.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('start_datetime', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]))
                ->badge(fn () => \App\Models\Event::whereBetween('start_datetime', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])->count()),
            'this_month' => Tab::make(__('event.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('start_datetime', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]))
                ->badge(fn () => \App\Models\Event::whereBetween('start_datetime', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ])->count()),
            'past' => Tab::make(__('event.tabs.past'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('end_datetime', '<', now()))
                ->badge(fn () => \App\Models\Event::where('end_datetime', '<', now())->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('event.actions.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
