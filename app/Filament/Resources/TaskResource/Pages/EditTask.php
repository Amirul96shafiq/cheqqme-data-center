<?php
namespace App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Forms;
use Filament\Pages\Actions;
use App\Filament\Pages\ActionBoard;
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
    // Limit top title to 20 characters
    $title = parent::getTitle();
    return mb_strimwidth($title, 0, 50, '...');
  }
  // Limit title length
  protected function mutateFormDataBeforeFill(array $data): array
  {
    if (isset($data['title'])) {
      $data['title'] = mb_substr($data['title'], 0, 100);
    }
    return $data;
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
        ->label('Save changes')
        ->color('primary')
        ->action('save'),
      Actions\Action::make('cancel')
        ->label('Cancel')
        ->url('/admin/action-board')
        ->color('gray'),
      Actions\DeleteAction::make()
        ->color('secondary'),
    ];
  }
}
