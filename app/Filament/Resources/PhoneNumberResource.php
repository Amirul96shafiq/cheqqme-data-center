<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhoneNumberResource\Pages;
use App\Filament\Resources\PhoneNumberResource\RelationManagers\PhoneNumberActivityLogRelationManager;
use App\Models\PhoneNumber;
use Closure;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class PhoneNumberResource extends Resource
{
    protected static ?string $model = PhoneNumber::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $recordTitleAttribute = 'title'; // Use 'title' as the record title attribute

    public static function getGloballySearchableAttributes(): array // This method defines which attributes are searchable globally
    {
        return ['title', 'phone'];
    }

    public static function getGlobalSearchResultDetails($record): array // This method defines the details shown in global search results
    {
        return [
            __('phonenumber.search.phone') => $record->phone,
        ];
    }

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
                        /*TextInput::make('phone')
                            ->label(__('phonenumber.form.phone_number'))
                            ->required()
                            ->tel(),*/
                        PhoneInput::make('phone')
                            ->label(__('phonenumber.form.phone_number'))
                            ->required()
                            ->countryStatePath('phone_country')
                            // ->inputNumberFormat(PhoneInputNumberType::E164)
                            // ->focusNumberFormat(PhoneInputNumberType::E164)
                            ->initialCountry('MY')
                            ->countryOrder(['MY', 'ID', 'SG', 'PH', 'US'])
                            ->onlyCountries(['MY', 'ID', 'SG', 'PH', 'US'])
                            // ->autoPlaceholder('aggressive')
                            ->countrySearch(false)
                            ->dropdownContainer(false)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                $digits = preg_replace('/\D+/', '', (string) $state);
                                if ($digits === '') {
                                    $set('phone', '');

                                    return;
                                }

                                $country = $get('phone_country') ?: 'MY';
                                $dialCode = match ($country) {
                                    'MY' => '60',
                                    'ID' => '62',
                                    'SG' => '65',
                                    'PH' => '63',
                                    'US' => '1',
                                    default => '60',
                                };

                                if (! str_starts_with($digits, $dialCode)) {
                                    $digits = ltrim($digits, '0');
                                    if (! str_starts_with($digits, $dialCode)) {
                                        $digits = $dialCode.$digits;
                                    }
                                }

                                $set('phone', $digits);
                            })
                            ->dehydrateStateUsing(function (?string $state, Get $get): string {
                                $digits = preg_replace('/\D+/', '', (string) $state);
                                if ($digits === '') {
                                    return '';
                                }

                                $country = $get('phone_country') ?: 'MY';
                                $dialCode = match ($country) {
                                    'MY' => '60',
                                    'ID' => '62',
                                    'SG' => '65',
                                    'PH' => '63',
                                    'US' => '1',
                                    default => '60',
                                };

                                if (! str_starts_with($digits, $dialCode)) {
                                    $digits = ltrim($digits, '0');
                                    if (! str_starts_with($digits, $dialCode)) {
                                        $digits = $dialCode.$digits;
                                    }
                                }

                                return $digits;
                            }),
                    ])
                    ->columns(2),

                Section::make()
                    ->heading(function (Get $get) {
                        $count = 0;

                        // Add count of extra_information items
                        $extraInfo = $get('extra_information') ?? [];
                        $count += count($extraInfo);

                        $title = __('phonenumber.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new HtmlString($title.' '.$badge);
                    })
                    ->collapsible(true)
                    ->live()
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
                            // ->maxLength(500)
                            ->extraAttributes([
                                'style' => 'resize: vertical;',
                            ])
                            ->live()
                            // Character limit helper text
                            ->helperText(function (Get $get) {
                                $raw = $get('notes') ?? '';
                                if (empty($raw)) {
                                    return __('phonenumber.form.notes_helper', ['count' => 500]);
                                }

                                // Optimized character counting - strip tags and count
                                $textOnly = strip_tags($raw);
                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                return __('phonenumber.form.notes_helper', ['count' => $remaining]);
                            })
                            // Block save if over 500 visible characters
                            ->rule(function (Get $get): Closure {
                                return function (string $attribute, $value, Closure $fail) {
                                    if (empty($value)) {
                                        return;
                                    }
                                    $textOnly = strip_tags($value);
                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $textOnly = trim(preg_replace('/\s+/', ' ', $textOnly));
                                    if (mb_strlen($textOnly) > 500) {
                                        $fail(__('phonenumber.form.notes_warning'));
                                    }
                                };
                            })
                            ->nullable(),

                        Repeater::make('extra_information')
                            ->label(__('phonenumber.form.extra_information'))
                            // ->relationship('extra_information')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('phonenumber.form.extra_title'))
                                            ->maxLength(100)
                                            ->debounce(1000)
                                            ->columnSpanFull(),
                                        RichEditor::make('value')
                                            ->label(__('phonenumber.form.extra_value'))
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
                                            ->live()
                                            ->reactive()
                                            // Character limit reactive function
                                            ->helperText(function (Get $get) {
                                                $raw = $get('value') ?? '';
                                                if (empty($raw)) {
                                                    return __('phonenumber.form.notes_helper', ['count' => 500]);
                                                }

                                                // Optimized character counting - strip tags and count
                                                $textOnly = strip_tags($raw);
                                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                                return __('phonenumber.form.notes_helper', ['count' => $remaining]);
                                            })
                                            // Block save if over 500 visible characters
                                            ->rule(function (Get $get): Closure {
                                                return function (string $attribute, $value, Closure $fail) {
                                                    if (empty($value)) {
                                                        return;
                                                    }
                                                    $textOnly = strip_tags($value);
                                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $textOnly = trim(preg_replace('/\s+/', ' ', $textOnly));
                                                    if (mb_strlen($textOnly) > 500) {
                                                        $fail(__('phonenumber.form.notes_warning'));
                                                    }
                                                };
                                            })
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('phonenumber.form.add_extra_info'))
                            ->addActionAlignment(Alignment::Start)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('phonenumber.form.title_placeholder_short'))
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
            // Disable record URL and record action for all records
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                TextColumn::make('id')
                    ->label(__('phonenumber.table.id'))
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('phonenumber.table.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->tooltip(function ($record) {
                        return $record->title;
                    }),
                TextColumn::make('country_from_phone')
                    ->label(__('phonenumber.table.country'))
                    ->getStateUsing(function ($record) {
                        $phone = $record->phone ?? '';
                        $digits = preg_replace('/\D+/', '', $phone);

                        // Extract country code and determine country
                        if (str_starts_with($digits, '60')) {
                            return 'MY';
                        } elseif (str_starts_with($digits, '65')) {
                            return 'SG';
                        } elseif (str_starts_with($digits, '62')) {
                            return 'ID';
                        } elseif (str_starts_with($digits, '63')) {
                            return 'PH';
                        } elseif (str_starts_with($digits, '1')) {
                            return 'US';
                        }

                        return 'Unknown';
                    })
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label(__('phonenumber.table.phone_number'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('phonenumber.table.created_at'))
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('j/n/y, h:i A'))
                    ->sortable(),
                Tables\Columns\ViewColumn::make('updated_at')
                    ->label(__('phonenumber.table.updated_at_by'))
                    ->view('filament.resources.phone-number-resource.updated-by-column')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('country_code')
                    ->label(__('phonenumber.filter.country_code'))
                    ->form([
                        \Filament\Forms\Components\Select::make('country_code')
                            ->label(__('phonenumber.filter.country_code'))
                            ->multiple()
                            ->options(function () {
                                // Get distinct country codes from existing phone numbers
                                $phoneNumbers = PhoneNumber::whereNotNull('phone')
                                    ->where('phone', '!=', '')
                                    ->pluck('phone')
                                    ->unique();

                                $countryCodes = [];
                                $countryMapping = [
                                    '60' => 'Malaysia',
                                    '62' => 'Indonesia',
                                    '65' => 'Singapore',
                                    '63' => 'Philippines',
                                    '1' => 'United States',
                                ];

                                foreach ($phoneNumbers as $phone) {
                                    // Extract digits only (handles both +60 and 60 formats)
                                    $digitsOnly = preg_replace('/\D+/', '', $phone);

                                    if (empty($digitsOnly)) {
                                        continue;
                                    }

                                    // Try different length country codes
                                    $firstTwo = substr($digitsOnly, 0, 2);
                                    $firstOne = substr($digitsOnly, 0, 1);

                                    if (isset($countryMapping[$firstTwo])) {
                                        $countryCode = $firstTwo;
                                        $countryName = $countryMapping[$firstTwo];
                                    } elseif (isset($countryMapping[$firstOne])) {
                                        $countryCode = $firstOne;
                                        $countryName = $countryMapping[$firstOne];
                                    } else {
                                        continue;
                                    }

                                    if (! isset($countryCodes[$countryCode])) {
                                        $countryCodes[$countryCode] = "{$countryName} (+{$countryCode})";
                                    }
                                }

                                // Sort by country code
                                ksort($countryCodes);

                                return $countryCodes;
                            }),
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['country_code'])) {
                            return $query;
                        }

                        $selectedCodes = is_array($data['country_code']) ? $data['country_code'] : [$data['country_code']];

                        return $query->where(function ($q) use ($selectedCodes) {
                            foreach ($selectedCodes as $index => $code) {
                                $condition = function ($subQuery) use ($code) {
                                    $subQuery->where('phone', 'like', "+{$code}%")
                                        ->orWhere('phone', 'like', "{$code}%");
                                };

                                if ($index === 0) {
                                    $q->where($condition);
                                } else {
                                    $q->orWhere($condition);
                                }
                            }
                        });
                    }),

                TrashedFilter::make()
                    ->label(__('phonenumber.filter.trashed'))
                    ->searchable(), // To show trashed or only active
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make('Log'),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            PhoneNumberActivityLogRelationManager::class,
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
        return __('phonenumber.navigation_group'); // Grouping phone numbers under Resources
    }

    public static function getNavigationSort(): ?int
    {
        return 55; // Adjust the navigation sort order as needed
    }
}
