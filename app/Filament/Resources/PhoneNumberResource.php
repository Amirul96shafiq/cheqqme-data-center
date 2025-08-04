<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhoneNumberResource\Pages;
use App\Filament\Resources\PhoneNumberResource\RelationManagers;
use App\Models\PhoneNumber;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Closure;
use Filament\Forms\Components\{TextInput, Select, FileUpload, Radio, Textarea, Grid, RichEditor};
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

class PhoneNumberResource extends Resource
{
    protected static ?string $model = PhoneNumber::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('phonenumber.section.phone_number_info'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('phonenumber.form.phone_number_title'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('phonenumber.form.phone_number'))
                            ->required()
                            ->tel(),
                    ])
                    ->columns(2),

                Section::make(__('phonenumber.section.phone_number_extra_info'))
                    ->schema([
                        RichEditor::make('notes')
                            ->label(__('phonenumber.form.notes'))
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
                                return __("phonenumber.form.notes_helper", ['count' => $remaining]);
                            })
                            // Block save if over 500 visible characters
                            ->rule(function (Get $get): Closure {
                                return function (string $attribute, $value, Closure $fail) {
                                    $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                    if (mb_strlen($textOnly) > 500) {
                                        $fail(__("phonenumber.form.notes_warning"));
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
                TextColumn::make('id')->label(__('phonenumber.table.id'))->sortable()->limit(20),
                TextColumn::make('title')->label(__('phonenumber.table.title'))->searchable()->sortable()->limit(10),
                TextColumn::make('phone')->label(__('phonenumber.table.phone_number'))->searchable(),
                TextColumn::make('created_at')->label(__('phonenumber.table.created_at'))->dateTime('j/n/y, h:i A')->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('phonenumber.table.updated_at_by'))
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListPhoneNumbers::route('/'),
            'create' => Pages\CreatePhoneNumber::route('/create'),
            'edit' => Pages\EditPhoneNumber::route('/{record}/edit'),
        ];
    }
    public static function getNavigationLabel(): string
    {
        return __('phonenumber.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('phonenumber.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('phonenumber.labels.plural');
    }
    
    public static function getNavigationGroup(): ?string
    {
        return __('phonenumber.navigation_group'); // Grouping phone numbers under Data Management
    }

    public static function getNavigationSort(): ?int
    {
        return 55; // Adjust the navigation sort order as needed
    }
}
