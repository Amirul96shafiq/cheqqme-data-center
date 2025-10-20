<?php

namespace App\Filament\Resources\ImportantUrlResource\Pages;

use App\Filament\Resources\ImportantUrlResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListImportantUrls extends ListRecords
{
    protected static string $resource = ImportantUrlResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('importanturl.tabs.all')),

            'today' => Tab::make(__('importanturl.tabs.today'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereBetween('created_at', [
                        now()->startOfDay(),
                        now()->endOfDay(),
                    ]);
                }),

            'this_week' => Tab::make(__('importanturl.tabs.this_week'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]);
                }),

            'this_month' => Tab::make(__('importanturl.tabs.this_month'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                }),

            'this_year' => Tab::make(__('importanturl.tabs.this_year'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereYear('created_at', now()->year);
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('importanturl.actions.create'))
                ->icon('heroicon-o-plus'),
        ];
    }
}
