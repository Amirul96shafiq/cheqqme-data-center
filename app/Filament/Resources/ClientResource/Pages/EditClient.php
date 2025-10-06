<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\ClientResource;
use Filament\Actions;

class EditClient extends BaseEditRecord
{
    protected static string $resource = ClientResource::class;

    protected static string $view = 'filament.resources.client-resource.pages.edit-client';

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
        return __('client.labels.edit-client');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
