<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\UserResource;
use Filament\Actions;

class EditUser extends BaseEditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
