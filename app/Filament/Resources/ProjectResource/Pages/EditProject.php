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
        // Populate tracking tokens from related tasks
        $record = $this->record;

        if ($record && $record->id) {
            $tasks = \App\Models\Task::whereNotNull('tracking_token')
                ->whereJsonContains('project', (string) $record->id)
                ->get(['id', 'tracking_token', 'status']);

            $data['tracking_tokens'] = $tasks->map(function ($task) {
                return [
                    'task_id' => $task->id,
                    'token' => $task->tracking_token,
                    'status' => $task->status,
                    'edit_url' => \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task->id]),
                    'status_url' => route('issue-tracker.status', ['token' => $task->tracking_token]),
                ];
            })->toArray();
        } else {
            $data['tracking_tokens'] = [];
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
