<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
            ->whereNotNull('tracking_token')
            ->whereJsonContains('project', (string) $this->getOwnerRecord()->id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                TextColumn::make('tracking_token')
                    ->label(__('project.form.tracking_token'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->color('primary'),

                TextColumn::make('status')
                    ->label(__('project.form.task_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'issue_tracker' => __('action.status.issue_tracker'),
                        'todo' => __('action.status.todo'),
                        'in_progress' => __('action.status.in_progress'),
                        'toreview' => __('action.status.toreview'),
                        'completed' => __('action.status.completed'),
                        'archived' => __('action.status.archived'),
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'issue_tracker' => 'info',
                        'todo' => 'gray',
                        'in_progress' => 'warning',
                        'toreview' => 'primary',
                        'completed' => 'success',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('title')
                    ->label(__('task.form.title'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->title),

                TextColumn::make('created_at')
                    ->label(__('project.table.created_at'))
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('j/n/y, h:i A'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // No create action - tracking tokens are created automatically
            ])
            ->actions([
                Tables\Actions\Action::make('edit_task')
                    ->label(__('project.form.edit_task'))
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn ($record) => TaskResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => ! $record->trashed()),

                Tables\Actions\Action::make('view_status')
                    ->label(__('project.form.view_status'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('issue-tracker.status', ['token' => $record->tracking_token]))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => ! empty($record->tracking_token)),
            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->defaultSort('created_at', 'desc');
    }
}
