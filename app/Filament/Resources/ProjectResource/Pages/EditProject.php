<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\ProjectResource;
use Filament\Actions;

class EditProject extends BaseEditRecord
{
    protected static string $resource = ProjectResource::class;

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
        return __('project.labels.edit-project');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
