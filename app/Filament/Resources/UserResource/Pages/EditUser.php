<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Kenepa\ResourceLock\Resources\Pages\Concerns\UsesResourceLock;

class EditUser extends BaseEditRecord
{
    use UsesResourceLock;

    protected static string $resource = UserResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    public function getContentTabLabel(): ?string
    {
        return __('user.labels.edit-user');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
