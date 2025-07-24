<?php

namespace App\Filament\Resources\ColleagueResource\Pages;

use App\Filament\Resources\ColleagueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Pages\Base\BaseEditRecord;

class EditColleague extends BaseEditRecord
{
    protected static string $resource = ColleagueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
