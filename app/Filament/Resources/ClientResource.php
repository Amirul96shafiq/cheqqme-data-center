<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Components\Section;
use Closure;
use Filament\Forms\Components\{TextInput, Select, FileUpload, Radio, Textarea, RichEditor, Grid, Repeater};
use Filament\Support\Enums\ActionSize;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $recordTitleAttribute = 'pic_name'; // Use 'pic_name' as the record title attribute

    public static function getGloballySearchableAttributes(): array // This method defines which attributes are searchable globally
    {
        return ['pic_name', 'company_name', 'company_email', 'pic_email', 'pic_contact_number'];
    }

    public static function getGlobalSearchResultDetails($record): array // This method defines the details shown in global search results
    {
        return [
            __('client.search.pic_email') => $record->pic_email,
            __('client.search.pic_contact_number') => $record->pic_contact_number,
            __('client.search.company_name') => $record->company_name,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('client.section.client_info'))
                    ->schema([
                        TextInput::make('pic_name')
                            ->label(__('client.form.pic_name'))
                            ->required()
                            ->reactive()
                            ->debounce(500) // Delay the reaction so user can finish typing
                            ->extraAttributes([
                                'x-on:blur' => "
                                    if (\$refs.companyName && !\$refs.companyName.value) {
                                        \$refs.companyName.value = \$el.value;
                                        \$el.dispatchEvent(new Event('input')); // Force model update
                                        \$refs.companyName.dispatchEvent(new Event('input'));
                                    }
                                ",
                            ])
                            ->extraAlpineAttributes(['x-ref' => 'picName']),
                        TextInput::make('pic_email')->label(__('client.form.pic_email'))->email()->required(),
                        TextInput::make('pic_contact_number')->label(__('client.form.pic_contact_number'))->required()->tel(),
                    ])
                    ->columns(3),

                Section::make(__('client.section.company_info'))
                    ->schema([
                        TextInput::make('company_name')
                            ->label(__('client.form.company_name'))
                            ->nullable()
                            ->extraAlpineAttributes(['x-ref' => 'companyName'])
                            ->helperText(__('client.form.company_name_helper'))
                            ->placeholder(fn(callable $get) => $get('pic_name')),
                        TextInput::make('company_email')->label(__('client.form.company_email'))->email()->nullable(),
                        Textarea::make('company_address')->label(__('client.form.company_address'))->rows(2)->nullable(),
                        Textarea::make('billing_address')->label(__('client.form.billing_address'))->rows(2)->nullable(),
                    ])
                    ->columns(2),

                Section::make(__('client.section.extra_info'))
                    ->schema([
                        RichEditor::make('notes')
                            ->label(__('client.form.notes'))
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
                                return __("client.form.notes_helper", ['count' => $remaining]);
                            })
                            // Block save if over 500 visible characters
                            ->rule(function (Get $get): Closure {
                                return function (string $attribute, $value, Closure $fail) {
                                    $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                    if (mb_strlen($textOnly) > 500) {
                                        $fail(__("client.form.notes_warning"));
                                    }
                                };
                            })
                            ->nullable(),

                        Repeater::make('extra_information')
                            ->label(__('client.form.extra_information'))
                            //->relationship('extra_information')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('client.form.extra_title'))
                                            ->required()
                                            ->maxLength(100)
                                            ->columnSpan([
                                                'default' => 12, // full width on small screens
                                                'md' => 4,       // 1/3 of 12 columns on medium and up
                                            ]),
                                        RichEditor::make('value')
                                            ->label(__('client.form.extra_value'))
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
                                            ->extraAttributes([
                                                'style' => 'resize: vertical;',
                                            ])
                                            ->reactive()
                                            //Character limit reactive function
                                            ->helperText(function (Get $get) {
                                                $raw = $get('value') ?? '';
                                                // 1. Strip all HTML tags
                                                $noHtml = strip_tags($raw);
                                                // 2. Decode HTML entities (e.g., &nbsp; -> actual space)
                                                $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                // 3. Count as-is — includes normal spaces, line breaks, etc.
                                                $remaining = 500 - mb_strlen($decoded);
                                                return __("client.form.notes_helper", ['count' => $remaining]);
                                            })
                                            // Block save if over 500 visible characters
                                            ->rule(function (Get $get): Closure {
                                                return function (string $attribute, $value, Closure $fail) {
                                                    $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                    if (mb_strlen($textOnly) > 500) {
                                                        $fail(__("client.form.notes_warning"));
                                                    }
                                                };
                                            })
                                            ->nullable()
                                            ->columnSpan([
                                                'default' => 12, // full width on small screens
                                                'md' => 8,       // 1/3 of 12 columns on medium and up
                                            ]),
                                    ])
                                    ->columns(12),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('client.form.add_extra_info'))
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                            ->live()
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Disable record URL for trashed records
            ->recordUrl(fn($record) => $record->trashed() ? null : static::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')->label(__('client.table.id'))->sortable(),
                TextColumn::make('pic_name')->label(__('client.table.pic_name'))->searchable()->limit(20),
                TextColumn::make('company_name')->label(__('client.table.company_name'))->searchable()->limit(20),
                TextColumn::make('created_at')->label(__('client.table.created_at'))->dateTime('j/n/y, h:i A')->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('client.table.updated_at_by'))
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
                TrashedFilter::make(), // To show trashed or only active
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->hidden(fn($record) => $record->trashed()),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
    public static function getNavigationLabel(): string
    {
        return __('client.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('client.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('client.labels.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('client.navigation_group');
    }
    public static function getNavigationSort(): ?int
    {
        return 11; // Adjust the navigation sort order as needed
    }
}
