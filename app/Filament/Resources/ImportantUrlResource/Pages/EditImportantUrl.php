<?php

namespace App\Filament\Resources\ImportantUrlResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\ImportantUrlResource;
use Filament\Actions;

class EditImportantUrl extends BaseEditRecord
{
    protected static string $resource = ImportantUrlResource::class;

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
        return __('importanturl.labels.edit-important-url');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
