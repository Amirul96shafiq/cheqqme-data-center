<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\DocumentResource;
use Filament\Actions;

class EditDocument extends BaseEditRecord
{
    protected static string $resource = DocumentResource::class;

    protected static string $view = 'filament.resources.document-resource.pages.edit-document';

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
        return __('document.labels.edit-document');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
