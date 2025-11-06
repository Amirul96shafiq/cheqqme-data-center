<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\ProjectResource;
use Filament\Actions;

class EditProject extends BaseEditRecord
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.resources.project-resource.pages.edit-project';

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;

        // Populate issue tracker info
        if ($record && $record->issue_tracker_code) {
            $data['issue_tracker_info'] = [[
                'code' => $record->issue_tracker_code,
                'url' => route('issue-tracker.show', ['project' => $record->issue_tracker_code]),
            ]];
        } else {
            $data['issue_tracker_info'] = [];
        }

        return $data;
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
