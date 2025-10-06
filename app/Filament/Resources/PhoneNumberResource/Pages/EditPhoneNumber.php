<?php

namespace App\Filament\Resources\PhoneNumberResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\PhoneNumberResource;
use Filament\Actions;
 
class EditPhoneNumber extends BaseEditRecord
{
    protected static string $resource = PhoneNumberResource::class;

    protected static string $view = 'filament.resources.phone-number-resource.pages.edit-phone-number';

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    public function getContentTabLabel(): ?string
    {
        return __('phonenumber.labels.edit-phone-number');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
