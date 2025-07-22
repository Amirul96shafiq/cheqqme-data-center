<?php

namespace App\Filament\Resources\ColleagueResource\Pages;

use App\Filament\Resources\ColleagueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListColleagues extends ListRecords
{
    protected static string $resource = ColleagueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
