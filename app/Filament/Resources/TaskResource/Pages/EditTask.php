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
            Actions\Action::make('comment')
                ->label(__('task.action.comment'))
                ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                ->color('primary')
                ->visible(fn () => true) // Will be controlled by CSS for responsive behavior
                ->extraAttributes([
                    'class' => '2xl:hidden', // Hide on large screens and above
                    'onclick' => 'scrollToComments(); return false;', // Client-side scroll, prevent default action
                ]),
            Actions\Action::make('share_task')
                ->label(__('task.action.share'))
                ->icon('heroicon-o-share')
                ->color('gray')
                ->action(function () {
                    // Generate a shareable URL for the task using Filament's URL generator
                    $shareUrl = TaskResource::getUrl('edit', ['record' => $this->record->id]);

                    // Dispatch browser event to copy URL to clipboard using generic event
                    $this->dispatch('copy-to-clipboard', text: $shareUrl, message: __('task.notifications.share_title'));

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

    /**
     * Enable unsaved changes alert for this page
     */
    protected function hasUnsavedDataChangesAlert(): bool
    {
        return true;
    }

    /**
     * Locked titles that should be preserved from existing data for issue tracker tasks.
     */
    protected function getLockedTitles(): array
    {
        return ['Reporter Name', 'Communication Preference', 'Reporter Email', 'Reporter WhatsApp', 'Submitted on'];
    }

    /**
     * Filter out empty items from extra_information array.
     */
    protected function filterEmptyItems(array $items): array
    {
        return array_values(array_filter($items, function ($item) {
            if (! is_array($item)) {
                return false;
            }

            $title = trim($item['title'] ?? '');
            $value = trim(strip_tags($item['value'] ?? ''));

            return ! empty($title) || ! empty($value);
        }));
    }

    /**
     * Create a unique key for an item based on title and value.
     */
    protected function getItemKey(array $item): string
    {
        $title = trim($item['title'] ?? '');
        $value = trim(strip_tags($item['value'] ?? ''));

        return $title.'|'.$value;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Filter out empty items from extra_information before filling the form
        if (isset($data['extra_information']) && is_array($data['extra_information'])) {
            $data['extra_information'] = $this->filterEmptyItems($data['extra_information']);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        $record = $this->record;
        if (! $record) {
            return $data;
        }

        // Get and filter existing data from record
        $existingExtraInfo = [];
        if ($record->extra_information) {
            $existingExtraInfo = is_array($record->extra_information)
                ? $this->filterEmptyItems($record->extra_information)
                : [];
        }

        // Get and filter form data
        $formExtraInfo = [];
        if (isset($data['extra_information']) && is_array($data['extra_information'])) {
            $formExtraInfo = $this->filterEmptyItems($data['extra_information']);
        }

        // For issue tracker tasks, merge existing data with form data
        if ($record->tracking_token) {
            $data['extra_information'] = $this->mergeExtraInformationForIssueTracker(
                $existingExtraInfo,
                $formExtraInfo
            );
        } else {
            // For regular tasks, use form data if available, otherwise preserve existing
            $data['extra_information'] = ! empty($formExtraInfo) ? $formExtraInfo : $existingExtraInfo;
        }

        return $data;
    }

    /**
     * Merge existing and form extra_information for issue tracker tasks.
     * Preserves locked items from existing data and adds new items from form data without duplicates.
     */
    protected function mergeExtraInformationForIssueTracker(array $existingExtraInfo, array $formExtraInfo): array
    {
        $lockedTitles = $this->getLockedTitles();

        // If no form data, return existing data
        if (empty($formExtraInfo)) {
            return $existingExtraInfo;
        }

        $merged = [];
        $seenItems = [];

        // First, add all locked items from existing data (they should always be preserved)
        foreach ($existingExtraInfo as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim($item['title'] ?? '');
            if (in_array($title, $lockedTitles, true)) {
                $merged[] = $item;
                $seenItems[] = $this->getItemKey($item);
            }
        }

        // Then, add form data (avoid duplicates by checking title+value)
        foreach ($formExtraInfo as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim($item['title'] ?? '');
            $itemKey = $this->getItemKey($item);

            // Skip if this item (title+value combination) is already in merged
            if (in_array($itemKey, $seenItems, true)) {
                continue;
            }

            // Skip if this is a locked title that's already in merged (from existing data)
            if (in_array($title, $lockedTitles, true)) {
                $hasLockedTitle = false;
                foreach ($merged as $mergedItem) {
                    if (is_array($mergedItem) && trim($mergedItem['title'] ?? '') === $title) {
                        $hasLockedTitle = true;
                        break;
                    }
                }
                if ($hasLockedTitle) {
                    continue;
                }
            }

            // Add the item
            $merged[] = $item;
            $seenItems[] = $itemKey;
        }

        return $merged;
    }
}
