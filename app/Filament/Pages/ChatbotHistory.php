<?php

namespace App\Filament\Pages;

use App\Models\ChatbotBackup;
use App\Services\ChatbotBackupService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ChatbotHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string $view = 'filament.pages.chatbot-history';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static ?string $slug = 'chatbot-history';

    // Disable navigation (will be added to profile menu instead)
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    // Get navigation label and title
    public static function getNavigationLabel(): string
    {
        return __('chatbot.history.navigation_label');
    }

    // Get title
    public function getTitle(): string
    {
        return __('chatbot.history.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ChatbotBackup::query()->where('user_id', Auth::id()))
            ->columns([
                TextColumn::make('id')
                    ->label(__('chatbot.table.backup_id')),

                TextColumn::make('backup_name')
                    ->label(__('chatbot.table.backup_name'))
                    ->sortable()
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->backup_name),

                TextColumn::make('backup_type')
                    ->label(__('chatbot.table.backup_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'primary',
                        'weekly' => 'success',
                        'import' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manual' => __('chatbot.filter.types.manual'),
                        'weekly' => __('chatbot.filter.types.weekly'),
                        'import' => __('chatbot.filter.types.import'),
                        default => ucfirst($state),
                    }),

                TextColumn::make('message_count')
                    ->label(__('chatbot.table.backup_messages'))
                    ->badge()
                    ->alignCenter(),

                TextColumn::make('formatted_date_range')
                    ->label(__('chatbot.table.backup_date_range'))
                    ->searchable(),

                TextColumn::make('backup_date')
                    ->label(__('chatbot.table.backup_backed_up'))
                    ->dateTime('j/n/y, h:i A')
                    ->sortable(),

                TextColumn::make('file_size')
                    ->label(__('chatbot.table.backup_size'))
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('time_period')
                    ->label(__('chatbot.filter.time_period'))
                    ->options([
                        'today' => __('chatbot.tabs.today'),
                        'this_week' => __('chatbot.tabs.this_week'),
                        'this_month' => __('chatbot.tabs.this_month'),
                        'this_year' => __('chatbot.tabs.this_year'),
                    ])
                    ->placeholder(__('chatbot.tabs.all'))
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'today' => $query->whereBetween('backup_date', [
                                now()->startOfDay(),
                                now()->endOfDay(),
                            ]),
                            'this_week' => $query->whereBetween('backup_date', [
                                now()->startOfWeek(),
                                now()->endOfWeek(),
                            ]),
                            'this_month' => $query->whereMonth('backup_date', now()->month)
                                ->whereYear('backup_date', now()->year),
                            'this_year' => $query->whereYear('backup_date', now()->year),
                            default => $query,
                        };
                    }),

                SelectFilter::make('backup_type')
                    ->label(__('chatbot.filter.backup_type'))
                    ->options([
                        'weekly' => __('chatbot.filter.types.weekly'),
                        'manual' => __('chatbot.filter.types.manual'),
                        'import' => __('chatbot.filter.types.import'),
                    ])
                    ->placeholder(__('chatbot.filter.all_types'))
                    ->searchable(),
            ])
            ->actions([
                ActionGroup::make([
                    TableAction::make('download')
                        ->label(__('chatbot.actions.download'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading(__('chatbot.confirm.backup_download'))
                        ->modalDescription(__('chatbot.confirm.backup_download_description'))
                        ->action(function (ChatbotBackup $record) {
                            $this->downloadBackup($record->id);
                        }),

                    TableAction::make('restore')
                        ->label(__('chatbot.actions.restore'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('chatbot.confirm.backup_restore'))
                        ->modalDescription(__('chatbot.confirm.backup_restore_description'))
                        ->action(function (ChatbotBackup $record) {
                            $this->restoreBackup($record->id);
                        }),

                    TableAction::make('delete')
                        ->label(__('chatbot.actions.delete'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(__('chatbot.confirm.backup_delete'))
                        ->modalDescription(__('chatbot.confirm.backup_delete_description'))
                        ->action(function (ChatbotBackup $record) {
                            $this->deleteBackup($record->id);
                        }),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label(__('chatbot.actions.delete'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(__('chatbot.confirm.backup_delete'))
                        ->modalDescription(__('chatbot.confirm.backup_delete_description'))
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $this->deleteBackup($record->id);
                            }
                        }),
                ]),
            ])
            ->defaultSort('backup_date', 'desc')
            ->emptyStateHeading(__('chatbot.empty.no_backups'))
            ->emptyStateDescription(__('chatbot.empty.no_backups_description'))
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_backup')
                ->label(__('chatbot.history.create_backup'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(__('chatbot.confirm.backup_creation'))
                ->modalDescription(__('chatbot.confirm.backup_description'))
                ->action(function () {
                    $this->createBackup();
                }),
        ];
    }

    public function downloadBackup($backupId)
    {
        $backup = ChatbotBackup::where('id', $backupId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $backup) {
            Notification::make()
                ->title(__('settings.backups.not_found'))
                ->body(__('settings.backups.not_found_body'))
                ->danger()
                ->send();

            return;
        }

        $backupService = new ChatbotBackupService;
        $fileName = $backupService->downloadBackup($backup);

        // Dispatch browser event with backup data
        $this->dispatch('download-backup', [
            'data' => $backup->backup_data,
            'filename' => $fileName,
        ]);

        Notification::make()
            ->title(__('settings.downloads.started'))
            ->body(__('settings.downloads.started_body', ['name' => $backup->backup_name]))
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
                ->title(__('settings.backups.not_found'))
                ->body(__('settings.backups.not_found_body'))
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
                ->title(__('settings.backups.restore_failed'))
                ->body(__('settings.backups.restore_failed_body', ['error' => $e->getMessage()]))
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
                ->title(__('settings.backups.not_found'))
                ->body(__('settings.backups.not_found_body'))
                ->danger()
                ->send();

            return;
        }

        try {
            $backupService = new ChatbotBackupService;
            $restoredCount = $backupService->restoreFromBackup($backup, $conversationId);

            Notification::make()
                ->title(__('settings.backups.restored'))
                ->body(__('settings.backups.restored_body', ['count' => $restoredCount]))
                ->success()
                ->send();

            // Dispatch event to refresh chatbot conversation list
            $this->dispatch('backup-restored');
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('settings.backups.restore_failed'))
                ->body(__('settings.backups.restore_failed_body', ['error' => $e->getMessage()]))
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
                ->title(__('settings.backups.not_found'))
                ->body(__('settings.backups.not_found_body'))
                ->danger()
                ->send();

            return;
        }

        $backupName = $backup->backup_name;
        $backup->delete();

        Notification::make()
            ->title(__('settings.backups.deleted'))
            ->body(__('settings.backups.deleted_body', ['name' => $backupName]))
            ->success()
            ->send();
    }

    public function createBackup(): void
    {
        try {
            $user = Auth::user();
            $backupService = new ChatbotBackupService;
            $backup = $backupService->createBackup($user, 'manual');

            Notification::make()
                ->title(__('settings.backups.created'))
                ->body(__('settings.backups.created_body', ['name' => $backup->backup_name]))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('settings.backups.failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
