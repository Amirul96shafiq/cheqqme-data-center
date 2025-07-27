<?php

namespace App\Filament\Resources\ImportantUrlResource\Pages;

use App\Filament\Resources\ImportantUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;


class ListImportantUrls extends ListRecords
{
    protected static string $resource = ImportantUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New Important URL'),
        ];
    }
}
