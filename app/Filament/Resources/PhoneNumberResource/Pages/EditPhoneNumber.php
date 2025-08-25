<?php

namespace App\Filament\Resources\PhoneNumberResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\PhoneNumberResource;
use Filament\Actions;

class EditPhoneNumber extends BaseEditRecord
{
    protected static string $resource = PhoneNumberResource::class;

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
