<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\ClientResource;
use Filament\Actions;

class EditClient extends BaseEditRecord
{
    protected static string $resource = ClientResource::class;

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
