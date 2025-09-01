<?php

namespace App\Filament\Resources\TrelloBoardResource\Pages;

use App\Filament\Resources\TrelloBoardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrelloBoards extends ListRecords
{
    protected static string $resource = TrelloBoardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
