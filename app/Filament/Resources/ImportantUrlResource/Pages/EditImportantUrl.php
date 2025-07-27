<?php

namespace App\Filament\Resources\ImportantUrlResource\Pages;

use App\Filament\Resources\ImportantUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Pages\Base\BaseEditRecord;

class EditImportantUrl extends BaseEditRecord
{
    protected static string $resource = ImportantUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
