<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\DocumentResource;
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
use Illuminate\Support\HtmlString;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('project.section.project_documents');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->documents()->count();

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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['project', 'createdBy', 'updatedBy'])->visibleToUser())
            ->columns([

                TextColumn::make('id')
                    ->label(__('document.table.id'))
                    ->url(fn ($record) => route('filament.admin.resources.documents.edit', $record->id))
                    ->sortable(),

                ViewColumn::make('title')
                    ->label(__('document.table.title'))
                    ->view('filament.resources.document-resource.title-column')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label(__('document.table.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'internal' => __('document.table.internal'),
                        'external' => __('document.table.external'),
                        default => ucfirst($state),
                    })
                    ->toggleable(),

                ViewColumn::make('file_type')
                    ->label(__('document.table.file_type'))
                    ->view('filament.resources.document-resource.file-type-column')
                    ->toggleable(),

                TextColumn::make('visibility_status')
                    ->label(__('document.table.visibility_status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => __('document.table.visibility_status_active'),
                        'draft' => __('document.table.visibility_status_draft'),
                        default => $state,
                    })
                    ->toggleable()
                    ->visible(true)
                    ->alignment(Alignment::Center),

                TextColumn::make('url')
                    ->label(__('document.table.document_url'))
                    ->state(function ($record) {
                        if ($record->type === 'external') {
                            return $record->url ?: '-';
                        }

                        if ($record->type === 'internal') {
                            return $record->file_path ? asset('storage/'.ltrim($record->file_path, '/')) : '-';
                        }

                        return '-';
                    })
                    ->url(function ($record) {
                        if ($record->type === 'external' && $record->url) {
                            return $record->url;
                        }

                        if ($record->type === 'internal' && $record->file_path) {
                            return asset('storage/'.ltrim($record->file_path, '/'));
                        }

                        return null;
                    })
                    ->openUrlInNewTab()
                    ->copyable()
                    ->limit(40)
                    ->tooltip(function ($record) {
                        if ($record->type === 'external') {
                            return $record->url ? __('document.tooltip.external_url', ['url' => $record->url]) : __('document.tooltip.no_url');
                        }

                        if ($record->type === 'internal') {
                            return $record->file_path ? __('document.tooltip.internal_file', ['path' => $record->file_path]) : __('document.tooltip.no_file');
                        }

                        return __('document.tooltip.unknown_type');
                    })
                    ->searchable(query: function ($query, string $search) {
                        // Search both url (for external) and file_path (for internal) columns
                        return $query->where(function ($q) use ($search) {
                            $q->where('url', 'like', "%{$search}%")
                                ->orWhere('file_path', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('created_at')
                    ->label(__('document.table.created_at_by'))
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
                    ->sortable(),

                ViewColumn::make('updated_at')
                    ->label(__('document.table.updated_at_by'))
                    ->view('filament.resources.document-resource.updated-by-column')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('document.table.type'))
                    ->options([
                        'internal' => __('document.table.internal'),
                        'external' => __('document.table.external'),
                    ])
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('file_type')
                    ->label(__('document.table.file_type'))
                    ->options([
                        'jpg' => 'JPG',
                        'png' => 'PNG',
                        'pdf' => 'PDF',
                        'docx' => 'DOCX',
                        'doc' => 'DOC',
                        'xlsx' => 'XLSX',
                        'xls' => 'XLS',
                        'pptx' => 'PPTX',
                        'ppt' => 'PPT',
                        'csv' => 'CSV',
                        'mp4' => 'MP4',
                        'url' => 'URL',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! filled($data['values'])) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($data) {
                            $conditions = [];

                            foreach ($data['values'] as $fileType) {
                                if ($fileType === 'url') {
                                    $conditions[] = fn (Builder $q) => $q->where('type', 'external');
                                } else {
                                    $extensions = match ($fileType) {
                                        'jpg' => ['jpg', 'jpeg'],
                                        'png' => ['png'],
                                        'pdf' => ['pdf'],
                                        'docx' => ['docx'],
                                        'doc' => ['doc'],
                                        'xlsx' => ['xlsx'],
                                        'xls' => ['xls'],
                                        'pptx' => ['pptx'],
                                        'ppt' => ['ppt'],
                                        'csv' => ['csv'],
                                        'mp4' => ['mp4'],
                                        default => [$fileType],
                                    };

                                    $conditions[] = function (Builder $q) use ($extensions) {
                                        $q->where('type', 'internal')
                                            ->where(function (Builder $subQuery) use ($extensions) {
                                                foreach ($extensions as $index => $ext) {
                                                    if ($index === 0) {
                                                        $subQuery->where('file_path', 'LIKE', '%.'.$ext);
                                                    } else {
                                                        $subQuery->orWhere('file_path', 'LIKE', '%.'.$ext);
                                                    }
                                                }
                                            });
                                    };
                                }
                            }

                            foreach ($conditions as $index => $condition) {
                                if ($index === 0) {
                                    $query->where($condition);
                                } else {
                                    $query->orWhere($condition);
                                }
                            }
                        });
                    })
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('visibility_status')
                    ->label(__('document.table.visibility_status'))
                    ->options([
                        'active' => __('document.table.visibility_status_active'),
                        'draft' => __('document.table.visibility_status_draft'),
                    ])
                    ->preload()
                    ->searchable(),

                TrashedFilter::make()
                    ->label(__('document.filter.trashed'))
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
                    ->url(function ($record) {
                        if ($record->type === 'internal' && $record->file_path) {
                            // For internal documents, use the uploaded file URL
                            return asset('storage/'.$record->file_path);
                        } elseif ($record->type === 'external' && $record->url) {
                            // For external documents, use the provided URL
                            return $record->url;
                        }

                        return null;
                    })
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        $url = '';
                        if ($record->type === 'internal' && $record->file_path) {
                            $url = asset('storage/'.$record->file_path);
                        } elseif ($record->type === 'external' && $record->url) {
                            $url = $record->url;
                        }

                        return strlen($url) > 50 ? substr($url, 0, 47).'...' : $url;
                    })
                    ->visible(function ($record) {
                        // Only show the action if there's a valid URL or file
                        return ($record->type === 'internal' && $record->file_path) ||
                            ($record->type === 'external' && $record->url);
                    }),

                Tables\Actions\ViewAction::make()
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => DocumentResource::getUrl('edit', ['record' => $record]))
                    ->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('toggle_visibility_status')
                        ->label(fn ($record) => $record->visibility_status === 'active'
                            ? __('document.actions.make_draft')
                            : __('document.actions.make_active'))
                        ->icon(fn ($record) => $record->visibility_status === 'active' ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn ($record) => $record->visibility_status === 'active' ? 'warning' : 'success')
                        ->action(function ($record) {
                            $newStatus = $record->visibility_status === 'active' ? 'draft' : 'active';

                            $record->update([
                                'visibility_status' => $newStatus,
                                'updated_by' => auth()->id(),
                            ]);

                            // Show success notification
                            \Filament\Notifications\Notification::make()
                                ->title(__('document.actions.visibility_status_updated'))
                                ->body($newStatus === 'active'
                                    ? __('document.actions.document_activated')
                                    : __('document.actions.document_made_draft'))
                                ->success()
                                ->send();
                        })
                        ->tooltip(fn ($record) => $record->visibility_status === 'active'
                            ? __('document.actions.make_draft_tooltip')
                            : __('document.actions.make_active_tooltip'))
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
                // Document Information Section (matches first section in form)
                Infolists\Components\Section::make(__('document.section.document_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label(__('document.form.document_title'))
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('project.title')
                            ->label(__('document.form.project'))
                            ->placeholder(__('No project assigned')),

                        Infolists\Components\TextEntry::make('type')
                            ->label(__('document.form.document_type'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'internal' => 'primary',
                                'external' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'internal' => __('document.form.internal'),
                                'external' => __('document.form.external'),
                                default => ucfirst($state),
                            }),

                        // Show URL for external documents
                        Infolists\Components\TextEntry::make('url')
                            ->label(__('document.form.document_url'))
                            ->copyable()
                            ->url(fn ($record) => $record->url)
                            ->openUrlInNewTab()
                            ->placeholder(__('No URL'))
                            ->visible(fn ($record) => $record->type === 'external')
                            ->columnSpanFull(),

                        // Show file path for internal documents
                        Infolists\Components\TextEntry::make('file_path')
                            ->label(__('document.form.document_upload'))
                            ->formatStateUsing(function ($state) {
                                if (! $state) {
                                    return __('No file uploaded');
                                }

                                $pathInfo = pathinfo($state);
                                $filename = $pathInfo['filename'] ?? '';
                                $extension = $pathInfo['extension'] ?? '';

                                // Limit filename to 30 characters and add truncation indicator
                                $truncatedFilename = strlen($filename) > 30 ? substr($filename, 0, 30).'...' : $filename;

                                return $truncatedFilename.'.'.$extension;
                            })
                            ->url(fn ($record) => $record->file_path ? asset('storage/'.$record->file_path) : null)
                            ->openUrlInNewTab()
                            ->visible(fn ($record) => $record->type === 'internal' && $record->file_path)
                            ->columnSpanFull(),
                    ]),

                // Additional Information Section (matches second section in form)
                Infolists\Components\Section::make()
                    ->heading(function ($record) {
                        $count = count($record->extra_information ?? []);

                        $title = __('document.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new HtmlString($title.' '.$badge);
                    })
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label(__('document.form.notes'))
                            ->markdown()
                            ->placeholder(__('No notes'))
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('extra_information')
                            ->label(__('document.form.extra_information'))
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label(__('document.form.extra_title')),
                                Infolists\Components\TextEntry::make('value')
                                    ->label(__('document.form.extra_value'))
                                    ->markdown(),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                // Visibility Status Information Section (matches third section in form)
                Infolists\Components\Section::make(__('document.section.visibility_status'))
                    ->schema([
                        Infolists\Components\TextEntry::make('visibility_status')
                            ->label(__('document.form.visibility_status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'draft' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => __('document.form.visibility_status_active'),
                                'draft' => __('document.form.visibility_status_draft'),
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
