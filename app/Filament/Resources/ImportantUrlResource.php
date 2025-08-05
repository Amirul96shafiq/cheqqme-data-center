<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportantUrlResource\Pages;
use App\Filament\Resources\ImportantUrlResource\RelationManagers;
use App\Models\ImportantUrl;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Closure;
use Filament\Forms\Components\{TextInput, Select, FileUpload, Radio, Textarea, Grid, RichEditor};
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\PasswordInput;
use Filament\Forms\Components\Password;
use Illuminate\Support\Facades\Hash;
use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\{ViewAction, EditAction, DeleteAction, RestoreAction};
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImportantUrlResource extends Resource
{
    protected static ?string $model = ImportantUrl::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('importanturl.section.important_url_info'))->schema([
                    Grid::make('3')->schema([
                        TextInput::make('title')->label(__('importanturl.form.important_url_title'))->required()->maxLength(50),
                        Select::make('project_id')
                            ->label(__('importanturl.form.project'))
                            ->relationship('project', 'title')
                            ->preload()
                            ->searchable()
                            ->nullable(),

                        Select::make('client_id')
                            ->label(__('importanturl.form.client'))
                            ->relationship('client', 'company_name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                    ]),

                    TextInput::make('url')
                        ->label(__('importanturl.form.important_url'))
                        ->helperText(__('importanturl.form.important_url_note'))
                        ->required()
                        ->hintAction(
                                fn(Get $get) => blank($get('url')) ? null : Action::make('openUrl')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->label(__('importanturl.form.open_url'))
                                    ->url(fn() => $get('url'), true)
                                    ->tooltip(__('importanturl.form.important_url_helper'))
                            )
                        ->url(),
                ]),
                Section::make(__('importanturl.section.important_url_extra_info'))->schema([
                    RichEditor::make('notes')
                            ->label(__('importanturl.form.notes'))
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                                'bulletList',
                                'codeBlock',
                            ])
                            //->maxLength(500)
                            ->extraAttributes([
                                'style' => 'resize: vertical;',
                            ])
                            ->reactive()
                            //Character limit reactive function
                            ->helperText(function (Get $get) {
                                $raw = $get('notes') ?? '';
                                // 1. Strip all HTML tags
                                $noHtml = strip_tags($raw);
                                // 2. Decode HTML entities (e.g., &nbsp; -> actual space)
                                $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                // 3. Count as-is — includes normal spaces, line breaks, etc.
                                $remaining = 500 - mb_strlen($decoded);
                                return __("importanturl.form.notes_helper", ['count' => $remaining]);
                            })
                            // Block save if over 500 visible characters
                            ->rule(function (Get $get): Closure {
                                return function (string $attribute, $value, Closure $fail) {
                                    $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                    if (mb_strlen($textOnly) > 500) {
                                        $fail(__('importanturl.form.notes_warning'));
                                    }
                                };
                            })
                            ->nullable(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('importanturl.table.id'))->sortable(),

                TextColumn::make('title')->label(__('importanturl.table.title'))->sortable()->searchable()->limit(20),

                TextColumn::make('url')
                    ->label(__('importanturl.table.link'))
                    ->url(fn($record) => $record->url, true)
                    ->openUrlInNewTab()
                    ->copyable()
                    ->limit(20),

                TextColumn::make('project.title')
                    ->label(__('importanturl.table.project'))
                    ->sortable()
                    ->searchable()
                    ->limit(20),

                TextColumn::make('created_at')->label(__('importanturl.table.created_at'))->dateTime('j/n/y, h:i A')->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('importanturl.table.updated_at_by'))
                    ->formatStateUsing(function ($state, $record) {
                        // Show '-' if there's no update or updated_by
                        if (
                            !$record->updated_by ||
                            $record->updated_at?->eq($record->created_at)
                        ) {
                            return '-';
                        }

                        $user = $record->updatedBy;
                        $formattedName = 'Unknown';

                        if ($user) {
                            $parts = explode(' ', $user->name);
                            $first = array_shift($parts);
                            $initials = implode(' ', array_map(fn($p) => mb_substr($p, 0, 1) . '.', $parts));
                            $formattedName = trim($first . ' ' . $initials);
                        }

                        return $state?->format('j/n/y, h:i A') . " ({$formattedName})";
                    })
                    ->sortable()
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('client_id')->label(__('importanturl.filters.client_id'))->relationship('client', 'company_name'),
                SelectFilter::make('project_id')->label(__('importanturl.filters.project_id'))->relationship('project', 'title'),
                TrashedFilter::make(), // To show trashed or only active
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->hidden(fn ($record) => $record->trashed()),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportantUrls::route('/'),
            'create' => Pages\CreateImportantUrl::route('/create'),
            'edit' => Pages\EditImportantUrl::route('/{record}/edit'),
        ];
    }
    public static function getNavigationLabel(): string
    {
        return __('importanturl.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('importanturl.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('importanturl.labels.plural');
    }
    
    public static function getNavigationGroup(): ?string
    {
        return __('importanturl.navigation_group'); // Grouping imporant url under Data Management
    }

    public static function getNavigationSort(): ?int
    {
        return 44; // Adjust the navigation sort order as needed
    }
}
