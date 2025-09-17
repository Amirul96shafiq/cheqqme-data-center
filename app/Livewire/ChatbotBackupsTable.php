<?php

namespace App\Livewire;

use App\Models\ChatbotBackup;
use App\Services\ChatbotBackupService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatbotBackupsTable extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public int $visibleCount = 5; // number of backups to display initially / currently

    public bool $isLoadingMore = false; // loading state for show more button

    public string $search = ''; // search term for filtering backups

    public ?string $backupTypeFilter = null; // filter by backup type

    public function render()
    {
        $query = ChatbotBackup::where('user_id', Auth::id());

        // Apply search filter if search term is provided
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('backup_name', 'like', '%'.$this->search.'%')
                    ->orWhere('backup_type', 'like', '%'.$this->search.'%')
                    ->orWhere('formatted_date_range', 'like', '%'.$this->search.'%');
            });
        }

        // Apply backup type filter if selected
        if (! empty($this->backupTypeFilter)) {
            $query->where('backup_type', $this->backupTypeFilter);
        }

        // When searching or filtering, show all results. Otherwise, limit to visible count
        if (! empty($this->search) || ! empty($this->backupTypeFilter)) {
            $backups = $query->orderBy('backup_date', 'desc')->get();
        } else {
            $backups = $query->orderBy('backup_date', 'desc')
                ->take($this->visibleCount)
                ->get();
        }

        return view('livewire.chatbot-backups-table', [
            'backups' => $backups,
        ]);
    }

    // Get the total backups (filtered by search if applicable)
    public function getTotalBackupsProperty(): int
    {
        $query = ChatbotBackup::where('user_id', Auth::id());

        // Apply search filter if search term is provided
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('backup_name', 'like', '%'.$this->search.'%')
                    ->orWhere('backup_type', 'like', '%'.$this->search.'%')
                    ->orWhere('formatted_date_range', 'like', '%'.$this->search.'%');
            });
        }

        // Apply backup type filter if selected
        if (! empty($this->backupTypeFilter)) {
            $query->where('backup_type', $this->backupTypeFilter);
        }

        return $query->count();
    }

    // Show more backups
    public function showMore(): void
    {
        $this->isLoadingMore = true;

        $total = $this->totalBackups;
        $remaining = $total - $this->visibleCount;
        if ($remaining <= 0) {
            $this->isLoadingMore = false;

            return;
        }

        $this->visibleCount += min(5, $remaining);
        $this->isLoadingMore = false;
    }

    public function downloadBackup($backupId)
    {
        $backup = ChatbotBackup::where('id', $backupId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $backup) {
            Notification::make()
                ->title('Backup not found')
                ->body('The requested backup could not be found.')
                ->danger()
                ->send();

            return;
        }

        $backupService = new ChatbotBackupService;
        $fileName = $backupService->downloadBackup($backup);

        // Use js() method to execute JavaScript directly
        $jsonData = json_encode($backup->backup_data);
        $escapedFilename = addslashes($fileName);

        $this->js("
            console.log('Direct JS execution for download');
            try {
                const backupData = {$jsonData};
                const filename = '{$escapedFilename}';

                console.log('Backup data:', backupData);
                console.log('Filename:', filename);

                const jsonString = JSON.stringify(backupData, null, 2);
                const blob = new Blob([jsonString], { type: 'application/json' });

                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                URL.revokeObjectURL(link.href);
                console.log('Download completed successfully');
            } catch (error) {
                console.error('Download failed:', error);
            }
        ");

        Notification::make()
            ->title('Download Started')
            ->body("Downloading backup: {$backup->backup_name}")
            ->success()
            ->send();
    }

    public function restoreBackup($backupId)
    {
        $backup = ChatbotBackup::where('id', $backupId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $backup) {
            Notification::make()
                ->title('Backup not found')
                ->body('The requested backup could not be found.')
                ->danger()
                ->send();

            return;
        }

        try {
            // Get current conversation ID from frontend via JavaScript
            $this->dispatch('get-current-conversation-id');

            // Wait a moment for the frontend to respond, then proceed with restore
            $this->js('
                setTimeout(() => {
                    const conversationId = localStorage.getItem("chatbot_conversation_id_" + window.chatbotUserId);
                    console.log("Current conversation ID:", conversationId);
                    
                    // Call restore with the conversation ID
                    $wire.call("restoreBackupWithId", '.$backupId.', conversationId);
                }, 100);
            ');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Restore Failed')
                ->body('Failed to restore backup: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function restoreBackupWithId($backupId, $conversationId)
    {
        $backup = ChatbotBackup::where('id', $backupId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $backup) {
            Notification::make()
                ->title('Backup not found')
                ->body('The requested backup could not be found.')
                ->danger()
                ->send();

            return;
        }

        try {
            $backupService = new ChatbotBackupService;
            $restoredCount = $backupService->restoreFromBackup($backup, $conversationId);

            Notification::make()
                ->title('Backup Restored')
                ->body("Successfully restored {$restoredCount} conversations from backup.")
                ->success()
                ->send();

            // Dispatch event to refresh chatbot conversation list
            $this->dispatch('backup-restored');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Restore Failed')
                ->body('Failed to restore backup: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteBackup($backupId)
    {
        $backup = ChatbotBackup::where('id', $backupId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $backup) {
            Notification::make()
                ->title('Backup not found')
                ->body('The requested backup could not be found.')
                ->danger()
                ->send();

            return;
        }

        $backupName = $backup->backup_name;
        $backup->delete();

        Notification::make()
            ->title('Backup Deleted')
            ->body("Backup '{$backupName}' has been deleted.")
            ->success()
            ->send();
    }

    public function refreshBackups()
    {
        // This method is called when a new backup is created
        // The component will automatically re-render with updated data
        // No additional logic needed as the render() method fetches fresh data
    }

    // Clear search and reset visible count
    public function clearSearch(): void
    {
        $this->search = '';
        $this->visibleCount = 5;
    }

    // Updated search method that resets visible count when searching
    public function updatedSearch(): void
    {
        $this->visibleCount = 5; // Reset to initial count when searching
    }

    // Updated backup type filter method that resets visible count when filtering
    public function updatedBackupTypeFilter(): void
    {
        $this->visibleCount = 5; // Reset to initial count when filtering
    }

    // Clear all filters and reset visible count
    public function clearFilters(): void
    {
        $this->search = '';
        $this->backupTypeFilter = null;
        $this->visibleCount = 5;
    }

    // Check if any filters are active
    public function getHasActiveFiltersProperty(): bool
    {
        return ! empty($this->search) || ! empty($this->backupTypeFilter);
    }

    // Create backup with confirmation
    public function createBackup(): void
    {
        try {
            $user = Auth::user();
            $backupService = new ChatbotBackupService;
            $backup = $backupService->createBackup($user, 'manual');

            Notification::make()
                ->title(__('settings.notifications.backup_created'))
                ->body(__('settings.notifications.backup_created_body', ['name' => $backup->backup_name]))
                ->success()
                ->send();

            // Refresh the backups list
            $this->refreshBackups();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('settings.notifications.backup_failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Show confirmation modal using global modal system
    public function showCreateBackupConfirmation(): void
    {
        // Use JavaScript to directly call the modal function
        $this->js('window.showGlobalModal("createBackup")');
    }

    // Show restore backup confirmation modal
    public function showRestoreBackupConfirmation($backupId): void
    {
        $this->js('window.showRestoreBackupModal('.$backupId.')');
    }

    // Show delete backup confirmation modal
    public function showDeleteBackupConfirmation($backupId): void
    {
        $this->js('window.showDeleteBackupModal('.$backupId.')');
    }

    // Show download backup confirmation modal
    public function showDownloadBackupConfirmation($backupId): void
    {
        $this->js('window.showDownloadBackupModal('.$backupId.')');
    }
}
