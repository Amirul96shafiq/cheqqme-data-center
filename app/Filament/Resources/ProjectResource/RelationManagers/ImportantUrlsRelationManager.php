<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\ImportantUrlResource;
use App\Helpers\ClientFormatter;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class ImportantUrlsRelationManager extends RelationManager
{
    protected static string $relationship = 'importantUrls';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('project.section.important_urls');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->importantUrls()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // Show on both Edit and View (modal)
        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction(null)
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['client', 'project', 'createdBy', 'updatedBy'])->visibleToUser())
            ->columns([

                TextColumn::make('id')
                    ->label(__('importanturl.table.id'))
                    ->url(fn ($record) => $record->trashed() ? null : route('filament.admin.resources.important-urls.edit', $record->id))
                    ->sortable(),

                ViewColumn::make('title')
                    ->label(__('importanturl.table.title'))
                    ->view('filament.resources.important-url-resource.title-column')
                    ->sortable()
                    ->searchable(),

                ViewColumn::make('client_id')
                    ->label(__('importanturl.table.client'))
                    ->view('filament.resources.important-url-resource.client-column')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('url')
                    ->label(__('importanturl.table.important_url'))
                    ->state(function ($record) {
                        return $record->url ?: '-';
                    })
                    ->copyable()
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->url ?: '';
                    })
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('visibility_status')
                    ->label(__('importanturl.table.visibility_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('importanturl.table.visibility_status_active'),
                        'draft' => __('importanturl.table.visibility_status_draft'),
                        default => $state,
                    })
                    ->toggleable()
                    ->visible(true)
                    ->alignment(Alignment::Center),

                TextColumn::make('created_at')
                    ->label(__('importanturl.table.created_at_by'))
                    ->since()
                    ->tooltip(function ($record) {
                        $createdAt = $record->created_at;

                        if (! $createdAt) {
                            return null;
                        }

                        $formatted = $createdAt->format('j/n/y, h:i A');

                        $creatorName = null;

                        if (method_exists($record, 'createdBy')) {
                            $creator = $record->createdBy;
                            $creatorName = $creator?->short_name ?? $creator?->name;
                        }

                        return $creatorName ? $formatted.' ('.$creatorName.')' : $formatted;
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ViewColumn::make('updated_at')
                    ->label(__('importanturl.table.updated_at_by'))
                    ->view('filament.resources.important-url-resource.updated-by-column')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('client_id')
                    ->label(__('importanturl.filters.client_id'))
                    ->relationship('client', 'pic_name')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return ClientFormatter::formatClientDisplay($record->pic_name, $record->company_name);
                    })
                    ->preload()
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('visibility_status')
                    ->label(__('importanturl.table.visibility_status'))
                    ->options([
                        'active' => __('importanturl.table.visibility_status_active'),
                        'draft' => __('importanturl.table.visibility_status_draft'),
                    ])
                    ->preload()
                    ->searchable(),

                TrashedFilter::make()
                    ->label(__('importanturl.filter.trashed'))
                    ->searchable(),
            ])
            ->headerActions([
                // Intentionally empty to avoid creating from here unless needed
            ])
            ->actions([

                Tables\Actions\Action::make('open_url')
                    ->label('')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        $url = $record->url;

                        return strlen($url) > 50 ? substr($url, 0, 47).'...' : $url;
                    }),

                Tables\Actions\ViewAction::make()
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => ImportantUrlResource::getUrl('edit', ['record' => $record]))
                    ->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('toggle_visibility_status')
                        ->label(fn ($record) => $record->visibility_status === 'active'
                            ? __('importanturl.actions.make_draft')
                            : __('importanturl.actions.make_active'))
                        ->icon(fn ($record) => $record->visibility_status === 'active' ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn ($record) => $record->visibility_status === 'active' ? 'warning' : 'success')
                        ->action(function ($record) {
                            $newStatus = $record->visibility_status === 'active' ? 'draft' : 'active';
                            $record->update([
                                'visibility_status' => $newStatus,
                                'updated_by' => auth()->id(),
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title(__('importanturl.actions.visibility_status_updated'))
                                ->body($newStatus === 'active'
                                    ? __('importanturl.actions.important_url_activated')
                                    : __('importanturl.actions.important_url_made_draft'))
                                ->success()
                                ->send();
                        })
                        ->tooltip(fn ($record) => $record->visibility_status === 'active'
                            ? __('importanturl.actions.make_draft_tooltip')
                            : __('importanturl.actions.make_active_tooltip'))
                        ->hidden(fn ($record) => $record->trashed() || $record->created_by !== auth()->id()),

                    ActivityLogTimelineTableAction::make('Log'),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Important URL Information Section (matches first section in form)
                Infolists\Components\Section::make(__('importanturl.section.important_url_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label(__('importanturl.form.important_url_title'))
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('client.company_name')
                            ->label(__('importanturl.form.client'))
                            ->placeholder(__('No client assigned')),

                        Infolists\Components\TextEntry::make('project.title')
                            ->label(__('importanturl.form.project'))
                            ->placeholder(__('No project assigned')),

                        Infolists\Components\TextEntry::make('url')
                            ->label(__('importanturl.form.important_url'))
                            ->copyable()
                            ->url(fn ($record) => $record->url)
                            ->openUrlInNewTab()
                            ->placeholder(__('No URL'))
                            ->columnSpanFull(),
                    ]),

                // Additional Information Section (matches second section in form)
                Infolists\Components\Section::make()
                    ->heading(function ($record) {
                        $count = count($record->extra_information ?? []);

                        $title = __('importanturl.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new \Illuminate\Support\HtmlString($title.' '.$badge);
                    })
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label(__('importanturl.form.notes'))
                            ->markdown()
                            ->placeholder(__('No notes'))
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('extra_information')
                            ->label(__('importanturl.form.extra_information'))
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label(__('importanturl.form.extra_title')),
                                Infolists\Components\TextEntry::make('value')
                                    ->label(__('importanturl.form.extra_value'))
                                    ->markdown(),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                // Visibility Status Information Section (matches third section in form)
                Infolists\Components\Section::make(__('importanturl.section.visibility_status'))
                    ->schema([
                        Infolists\Components\TextEntry::make('visibility_status')
                            ->label(__('importanturl.form.visibility_status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'draft' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => __('importanturl.form.visibility_status_active'),
                                'draft' => __('importanturl.form.visibility_status_draft'),
                                default => $state,
                            }),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('createdBy.name')
                                    ->label(__('Created by'))
                                    ->placeholder(__('Unknown')),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label(__('Created at'))
                                    ->dateTime('j/n/y, h:i A'),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('updatedBy.name')
                                    ->label(__('Updated by'))
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->dateTime('j/n/y, h:i A'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
