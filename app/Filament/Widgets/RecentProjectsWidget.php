<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentProjectsWidget extends TableWidget
{
    protected function getTableQuery(): Builder
    {
        return Project::latest()->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [

            TextColumn::make('id')
                ->label(__('dashboard.recent_projects.id')),

            ViewColumn::make('title')
                ->label(__('dashboard.recent_projects.project_title'))
                ->view('filament.widgets.recent-projects-title-column'),

            TextColumn::make('tracking_tokens_count')
                ->label(__('dashboard.recent_projects.issues'))
                ->badge()
                ->copyable()
                ->copyableState(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                ->color('primary')
                ->url(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                ->alignCenter(),

            TextColumn::make('wishlist_tokens_count')
                ->label(__('dashboard.recent_projects.wishlist'))
                ->badge()
                ->copyable()
                ->copyableState(fn ($record) => $record->wishlist_tracker_code ? route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]) : null)
                ->color('success')
                ->url(fn ($record) => $record->wishlist_tracker_code ? route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]) : null)
                ->alignCenter(),

            TextColumn::make('created_at')
                ->label(__('dashboard.recent_projects.created_at'))
                ->dateTime('j/n/y, h:i A'),

        ];
    }

    protected function getTableActions(): array
    {
        return [

            Action::make('view')
                ->label('')
                ->icon('heroicon-o-link')
                ->url(fn (Project $record) => route('issue-tracker.show', ['project' => $record->issue_tracker_code]))
                ->visible(fn (Project $record) => filled($record->issue_tracker_code))
                ->openUrlInNewTab(),

            Action::make('viewWishlist')
                ->label('')
                ->icon('heroicon-o-link')
                ->color('success')
                ->url(fn (Project $record) => route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]))
                ->visible(fn (Project $record) => filled($record->wishlist_tracker_code))
                ->openUrlInNewTab(),

            EditAction::make()
                ->label(__('dashboard.actions.edit'))
                ->url(fn (Project $record) => route('filament.admin.resources.projects.edit', $record)),

        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Action::make('viewAll')
                ->label(label: __('dashboard.actions.view_all'))
                ->url(route('filament.admin.resources.projects.index'))
                ->icon('heroicon-m-arrow-right')
                ->button()
                ->color('gray')
                ->visible(fn () => Project::count() > 5),
        ];
    }

    // Heading for the widget
    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return __('dashboard.recent_projects.title');
    }
}
