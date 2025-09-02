<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Kenepa\ResourceLock\Resources\Pages\Concerns\UsesResourceLock;

class EditDocument extends BaseEditRecord
{
    use UsesResourceLock;

    protected static string $resource = DocumentResource::class;

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
