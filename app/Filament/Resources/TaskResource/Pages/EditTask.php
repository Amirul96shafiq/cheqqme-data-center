<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Pages\ActionBoard;
use App\Filament\Resources\TaskResource;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    // Use custom Blade template
    protected static string $view = 'filament.resources.task-resource.pages.edit-task';

    // Remove breadcrumb
    public function getBreadcrumb(): string
    {
        // Remove breadcrumb
        return '';
    }

    public function getTitle(): string
    {
        // Limit top title to 50 characters in the page header only
        $title = parent::getTitle();

        return mb_strimwidth($title, 0, 50, '...');
    }

    // Redirect to Action Board after save
    protected function getRedirectUrl(): string
    {
        return ActionBoard::getUrl();
    }

    // Redirect to Action Board after cancel
    protected function getCancelRedirectUrl(): ?string
    {
        return ActionBoard::getUrl();
    }

    // Remove all breadcrumbs
    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getCancelAction()
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('share_task')
                ->label(__('task.action.share'))
                ->icon('heroicon-o-share')
                ->color('gray')
                ->action(function () {
                    // Generate a shareable URL for the task using Filament's URL generator
                    $shareUrl = TaskResource::getUrl('edit', ['record' => $this->record->id]);

                    // Dispatch browser event to copy URL to clipboard
                    $this->dispatch('copy-task-url', url: $shareUrl);

                    // Show notification that copy operation was initiated
                    Notification::make()
                        ->title(__('task.notifications.share_title'))
                        ->body(__('task.notifications.share_body', ['url' => $shareUrl]))
                        ->icon('heroicon-o-clipboard')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label(__('task.action.save_changes'))
                ->color('primary')
                ->action('save'),
            Actions\Action::make('cancel')
                ->label(__('task.action.cancel'))
                ->url('/admin/action-board')
                ->color('gray'),
            Actions\DeleteAction::make()
                ->successRedirectUrl(ActionBoard::getUrl())
                ->successNotification(
                    Notification::make()
                        ->title(__('task.notifications.deleted_title'))
                        ->body(__('task.notifications.deleted_body', ['title' => $this->record->title]))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->success()
                ),
        ];
    }
}
