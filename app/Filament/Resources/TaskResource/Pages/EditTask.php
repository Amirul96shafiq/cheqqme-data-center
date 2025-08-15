<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Pages\ActionBoard;
use App\Filament\Resources\TaskResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
  protected static string $resource = TaskResource::class;

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
      Actions\DeleteAction::make(),
    ];
  }
}
