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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Filter out empty items from extra_information before filling the form
        if (isset($data['extra_information']) && is_array($data['extra_information'])) {
            $extraInfo = $data['extra_information'];

            // Filter out empty items (items with no title and no value)
            $filtered = array_filter($extraInfo, function ($item) {
                if (! is_array($item)) {
                    return false;
                }
                $title = trim($item['title'] ?? '');
                $value = trim(strip_tags($item['value'] ?? ''));

                return ! empty($title) || ! empty($value);
            });

            // Set the filtered data (preserve keys for Filament Repeater)
            $data['extra_information'] = array_values($filtered);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        $record = $this->record;

        // Get existing data from record
        $existingExtraInfo = [];
        if ($record && $record->extra_information) {
            $existingExtraInfo = is_array($record->extra_information) ? $record->extra_information : [];
            // Filter out empty items
            $existingExtraInfo = array_filter($existingExtraInfo, function ($item) {
                if (! is_array($item)) {
                    return false;
                }
                $title = trim($item['title'] ?? '');
                $value = trim(strip_tags($item['value'] ?? ''));

                return ! empty($title) || ! empty($value);
            });
        }

        // Get form data
        $formExtraInfo = [];
        if (isset($data['extra_information']) && is_array($data['extra_information'])) {
            $formExtraInfo = $data['extra_information'];
            // Filter out empty items
            $formExtraInfo = array_filter($formExtraInfo, function ($item) {
                if (! is_array($item)) {
                    return false;
                }
                $title = trim($item['title'] ?? '');
                $value = trim(strip_tags($item['value'] ?? ''));

                return ! empty($title) || ! empty($value);
            });
        }

        // For issue tracker tasks, merge existing data with form data
        // This ensures original reporter data is preserved while allowing new data to be added
        if ($record && $record->tracking_token) {
            // Identify locked titles that should be preserved from existing data
            $lockedTitles = ['Reporter Name', 'Communication Preference', 'Reporter Email', 'Reporter WhatsApp', 'Submitted on'];

            // If form has data, merge intelligently
            if (! empty($formExtraInfo)) {
                $merged = [];
                $seenItems = []; // Track seen items by title+value to prevent duplicates

                // First, add all locked items from existing data (they should always be preserved)
                foreach ($existingExtraInfo as $item) {
                    if (is_array($item)) {
                        $title = trim($item['title'] ?? '');
                        if (in_array($title, $lockedTitles, true)) {
                            $merged[] = $item;
                            // Create a unique key for this item (title + value)
                            $itemKey = $title.'|'.trim(strip_tags($item['value'] ?? ''));
                            $seenItems[] = $itemKey;
                        }
                    }
                }

                // Then, add form data (avoid ALL duplicates by checking title+value)
                foreach ($formExtraInfo as $item) {
                    if (is_array($item)) {
                        $title = trim($item['title'] ?? '');
                        $value = trim(strip_tags($item['value'] ?? ''));
                        $itemKey = $title.'|'.$value;

                        // Skip if this item (title+value combination) is already in merged
                        if (in_array($itemKey, $seenItems, true)) {
                            continue;
                        }

                        // Skip if this is a locked title that's already in merged (from existing data)
                        if (in_array($title, $lockedTitles, true)) {
                            // Check if we already have this locked title in merged
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
                }

                $data['extra_information'] = array_values($merged);
            } else {
                // If no form data, use existing data
                $data['extra_information'] = array_values($existingExtraInfo);
            }
        } else {
            // For regular tasks, use form data if available, otherwise preserve existing
            if (! empty($formExtraInfo)) {
                $data['extra_information'] = array_values($formExtraInfo);
            } elseif (! empty($existingExtraInfo)) {
                $data['extra_information'] = array_values($existingExtraInfo);
            } else {
                $data['extra_information'] = [];
            }
        }

        return $data;
    }
}
