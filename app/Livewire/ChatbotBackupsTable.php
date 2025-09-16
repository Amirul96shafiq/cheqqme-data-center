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

    public function render()
    {
        $backups = ChatbotBackup::where('user_id', Auth::id())
            ->orderBy('backup_date', 'desc')
            ->take($this->visibleCount)
            ->get();

        return view('livewire.chatbot-backups-table', [
            'backups' => $backups,
        ]);
    }

    // Get the total backups
    public function getTotalBackupsProperty(): int
    {
        return ChatbotBackup::where('user_id', Auth::id())->count();
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

        // Trigger download via JavaScript
        $this->dispatch('download-backup', [
            'url' => route('chatbot.backup.download', $backup->id),
            'filename' => $fileName,
        ]);

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
}
