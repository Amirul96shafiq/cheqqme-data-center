<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TrackingTokensRelationManager extends RelationManager
{
    protected static string $relationship = 'trackingTokens';

    protected static ?string $recordTitleAttribute = 'tracking_token';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('project.form.tracking_tokens');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = Task::whereNotNull('tracking_token')
            ->whereJsonContains('project', (string) $ownerRecord->id)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    protected function getTableQuery(): Builder
    {
        return Task::query()
            ->with('updatedBy')
            ->whereNotNull('tracking_token')
            ->whereJsonContains('project', (string) $this->getOwnerRecord()->id);
    }

    protected function getIssueStatusTextForCopy(Task $task, Model $project): string
    {
        $projectTitle = $project->title ?? 'TBD';
        $issueTitle = $task->title ?: __('project.actions.issue_status_no_title');
        $statusLabel = $this->formatIssueStatusLabel($task->status);
        $statusUrl = route('issue-tracker.status', ['token' => $task->tracking_token]);
        $submittedAt = $task->created_at?->format('j/n/y, h:i A');

        $submittedLine = $submittedAt ? "\n🔹 Reported On: {$submittedAt}" : '';

        return "Hi team 👋,\n\nHere's the latest status update for \"{$issueTitle}\" in the {$projectTitle} project.\n\n🔹 Tracking Token: {$task->tracking_token}\n🔹 Current Status: {$statusLabel}{$submittedLine}\n\nCheck the live status here:\n{$statusUrl}\n\nThank you!";
    }

    protected function formatIssueStatusLabel(?string $status): string
    {
        if (empty($status)) {
            return __('project.actions.issue_status_unknown');
        }

        $translationKey = "action.status.{$status}";
        $translated = __($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([

                TextColumn::make('id')
                    ->label(__('project.table.id'))
                    ->sortable(),

                TextColumn::make('tracking_token')
                    ->label(__('project.form.tracking_token'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyableState(fn (Task $record): string => route('issue-tracker.status', ['token' => $record->tracking_token]))
                    ->color('primary'),

                ViewColumn::make('title')
                    ->label(__('task.form.title'))
                    ->view('filament.resources.project-resource.title-column')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('project.form.task_status'))
                    ->badge()
                    ->formatStateUsing(function (string $state): string {
                        // Try to get translation from action.status, fallback to ucfirst if not found
                        $translationKey = "action.status.{$state}";
                        $translated = __($translationKey);

                        // If translation key doesn't exist, Laravel returns the key itself
                        // Check if translation exists by comparing with the key
                        if ($translated === $translationKey) {
                            return ucfirst(str_replace('_', ' ', $state));
                        }

                        return $translated;
                    })
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('project.table.created_at'))
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('j/n/y, h:i A'))
                    ->sortable(),

                ViewColumn::make('updated_at')
                    ->label(__('project.table.updated_at_by'))
                    ->view('filament.resources.document-resource.updated-by-column')
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('project.form.task_status'))
                    ->options(function () {
                        // Dynamically get all statuses from translation file
                        $statuses = __('action.status');
                        if (is_array($statuses)) {
                            return $statuses;
                        }

                        // Fallback to common statuses if translation structure is different
                        return [
                            'issue_tracker' => __('action.status.issue_tracker'),
                            'todo' => __('action.status.todo'),
                            'in_progress' => __('action.status.in_progress'),
                            'toreview' => __('action.status.toreview'),
                            'completed' => __('action.status.completed'),
                            'archived' => __('action.status.archived'),
                        ];
                    })
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->headerActions([
                // No create action - tracking tokens are created automatically
            ])
            ->actions([

                Tables\Actions\Action::make('view_status')
                    ->label('')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->url(fn ($record) => route('issue-tracker.status', ['token' => $record->tracking_token]))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => ! empty($record->tracking_token)),

                Tables\Actions\Action::make('edit_task')
                    ->label(__('project.form.edit_task'))
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn ($record) => TaskResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => ! $record->trashed()),

                Tables\Actions\ActionGroup::make([

                    Tables\Actions\Action::make('share_issue_status_link')
                        ->label(__('project.actions.share_issue_status_link'))
                        ->icon('heroicon-o-share')
                        ->color('primary')
                        ->visible(fn ($record) => ! $record->trashed() && ! empty($record->tracking_token))
                        ->modalWidth('2xl')
                        ->modalHeading(__('project.actions.share_issue_status_link'))
                        ->modalDescription(__('project.actions.share_issue_status_link_description'))
                        ->form(function (Task $record) {
                            $project = $this->getOwnerRecord();
                            $issueStatusText = $this->getIssueStatusTextForCopy($record, $project);

                            return [
                                Forms\Components\Textarea::make('issue_status_preview')
                                    ->label(__('project.actions.issue_status_preview'))
                                    ->default($issueStatusText)
                                    ->disabled()
                                    ->rows(12)
                                    ->extraInputAttributes([
                                        'class' => 'font-mono text-sm !resize-none',
                                        'style' => 'resize: none !important; max-height: none !important;',
                                        'x-init' => '$el.style.resize = "none"',
                                    ])
                                    ->columnSpanFull(),
                            ];
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->extraModalFooterActions(function (Task $record, $livewire) {
                            $actions = [];

                            // Detect mobile device and hide copy button on mobile
                            $userAgent = request()->userAgent() ?? '';
                            $isMobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);

                            // Only show copy button on desktop, hide on mobile so users can manually copy from preview
                            if (! $isMobile) {
                                $actions[] = Tables\Actions\Action::make('copy_to_clipboard')
                                    ->label(__('project.actions.copy_to_clipboard'))
                                    ->icon('heroicon-o-clipboard-document')
                                    ->color('primary')
                                    ->extraAttributes([
                                        'x-data' => '{}',
                                        'x-on:copy-success.window' => 'showCopiedBubble($el)',
                                    ])
                                    ->action(function () use ($record, $livewire) {
                                        $project = $this->getOwnerRecord();
                                        $issueStatusText = $this->getIssueStatusTextForCopy($record, $project);

                                        // Dispatch browser event with the text to copy and success callback
                                        $livewire->dispatch('copy-to-clipboard-with-callback', text: $issueStatusText);
                                    });
                            }

                            return $actions;
                        }),

                ]),

            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->defaultSort('created_at', 'desc');
    }
}
