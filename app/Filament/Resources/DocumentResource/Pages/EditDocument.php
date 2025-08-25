<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\DocumentResource;
use Filament\Actions;

class EditDocument extends BaseEditRecord
{
    protected static string $resource = DocumentResource::class;

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
