<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\ImportantUrlResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
            ->recordUrl(fn ($record) => $record->trashed() ? null : ImportantUrlResource::getUrl('edit', ['record' => $record]))
            ->modifyQueryUsing(fn ($query) => $query->with('client'))
            ->columns([
                TextColumn::make('id')
                    ->label(__('importanturl.table.id'))
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('importanturl.table.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('client.pic_name')
                    ->label(__('importanturl.table.client'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        // Format the client name (shorten if too long)
                        $formattedName = self::formatClientName($state);

                        // Format the company name (limit to 10 characters)
                        $formattedCompany = self::formatCompanyName($record->client?->company_name);

                        // Return the combined format: "Name (Company)"
                        return $formattedName.' ('.$formattedCompany.')';
                    }),
                TextColumn::make('important_url')
                    ->label(__('importanturl.table.important_url'))
                    ->state(function ($record) {
                        return $record->url ?: '-';
                    })
                    ->copyable()
                    ->limit(30),
                TextColumn::make('created_at')
                    ->label(__('importanturl.table.created_at'))
                    ->dateTime('j/n/y, h:i A')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('importanturl.table.updated_at_by'))
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->updated_by || $record->updated_at?->eq($record->created_at)) {
                            return '-';
                        }

                        $user = $record->updatedBy;
                        $formattedName = $user ? $user->short_name : 'Unknown';

                        return $state?->format('j/n/y, h:i A')." ({$formattedName})";
                    })
                    ->sortable()
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('client_id')
                    ->label(__('importanturl.table.client'))
                    ->relationship('client', 'pic_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->pic_name} ({$record->company_name})")
                    ->preload()
                    ->searchable()
                    ->multiple(),
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
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => ImportantUrlResource::getUrl('edit', ['record' => $record]))
                    ->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make('Log'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                // None for now
            ])
            ->defaultSort(function ($query) {
                return $query->orderByRaw('
                    CASE
                        WHEN updated_at IS NOT NULL AND updated_at != created_at THEN updated_at
                        ELSE created_at
                    END DESC
                ');
            });
    }

    /**
     * Format client name with shortening logic
     * Example: "Amirul Shafiq Harun" becomes "Amirul S. H."
     */
    private static function formatClientName(?string $name): string
    {
        if (empty($name)) {
            return '';
        }

        $parts = explode(' ', trim($name));

        // If only one word, return as is
        if (count($parts) === 1) {
            return $parts[0];
        }

        // If two words, return first word + first letter of second word
        if (count($parts) === 2) {
            return $parts[0].' '.substr($parts[1], 0, 1).'.';
        }

        // If three or more words, return first + middle initial + last initial
        $first = $parts[0];
        $last = end($parts); // Get the last element without removing it
        $middleInitial = '';

        // If there's a middle name, get its first letter
        if (count($parts) >= 3) {
            $middleInitial = substr($parts[1], 0, 1).'. ';
        }

        return $first.' '.$middleInitial.substr($last, 0, 1).'.';
    }

    /**
     * Format company name with character limit
     * Limits to 10 characters with ellipsis if longer
     */
    private static function formatCompanyName(?string $company): string
    {
        if (empty($company)) {
            return '';
        }

        // If company name is longer than 10 characters, truncate and add ellipsis
        if (strlen($company) > 10) {
            return substr($company, 0, 10).'...';
        }

        return $company;
    }
}
