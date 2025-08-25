<?php

namespace App\Filament\Resources\ImportantUrlResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\ImportantUrlResource;
use Filament\Actions;

class EditImportantUrl extends BaseEditRecord
{
    protected static string $resource = ImportantUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
