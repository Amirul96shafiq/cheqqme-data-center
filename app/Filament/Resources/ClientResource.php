<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Filament\Resources\ClientResource\RelationManagers\ClientActivityLogRelationManager;
use App\Models\Client;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Schmeits\FilamentCharacterCounter\Forms\Components\RichEditor;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

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
            __('client.search.pic_email') => $record->pic_email ?? '-',
            __('client.search.pic_contact_number') => $record->pic_contact_number ?? '-',
            __('client.search.company_name') => $record->company_name ?? '-',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('client.section.client_info'))
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                            '2xl' => 3,
                        ])->schema([

                            TextInput::make('pic_name')
                                ->label(__('client.form.pic_name'))
                                ->required()
                                ->maxLength(100)
                                ->reactive()
                                ->debounce(500) // Delay the reaction so user can finish typing
                                ->extraAttributes([
                                    'x-on:blur' => "
                                            if (\$refs.companyName && !\$refs.companyName.value) {
                                                \$refs.companyName.value = \$el.value;
                                                \$el.dispatchEvent(new Event('input')); // Force model update
                                                \$refs.companyName.dispatchEvent(new Event('input'));
                                                }
                                            }
                                        ",
                                ])
                                ->extraAlpineAttributes(['x-ref' => 'picName']),

                            PhoneInput::make('pic_contact_number')
                                ->label(__('client.form.pic_contact_number'))
                                ->required()
                                ->countryStatePath('pic_contact_number_country')
                                ->initialCountry('MY')
                                ->countryOrder(['MY', 'ID', 'SG', 'PH', 'US'])
                                ->onlyCountries(['MY', 'ID', 'SG', 'PH', 'US'])
                                ->countrySearch(false)
                                ->dropdownContainer(false)
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                    $digits = preg_replace('/\D+/', '', (string) $state);
                                    if ($digits === '') {
                                        $set('pic_contact_number', '');

                                        return;
                                    }

                                    $country = $get('pic_contact_number_country') ?: 'MY';
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

                                    $set('pic_contact_number', $digits);
                                })
                                ->dehydrateStateUsing(function (?string $state, Get $get): string {
                                    $digits = preg_replace('/\D+/', '', (string) $state);
                                    if ($digits === '') {
                                        return '';
                                    }

                                    $country = $get('pic_contact_number_country') ?: 'MY';
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

                            TextInput::make('pic_email')
                                ->label(__('client.form.pic_email'))
                                ->email()
                                ->nullable(),

                        ]),
                    ]),

                Section::make(__('client.section.staff_info'))
                    ->description(__('client.section.staff_info_description'))
                    ->schema([

                        Repeater::make('staff_information')
                            ->label(__('client.form.staff_information'))
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('staff_name')
                                            ->label(__('client.form.staff_name'))
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->columnSpan(1),

                                        PhoneInput::make('staff_contact_number')
                                            ->label(__('client.form.staff_contact_number'))
                                            ->countryStatePath('staff_contact_number_country')
                                            ->initialCountry('MY')
                                            ->countryOrder(['MY', 'ID', 'SG', 'PH', 'US'])
                                            ->onlyCountries(['MY', 'ID', 'SG', 'PH', 'US'])
                                            ->countrySearch(false)
                                            ->dropdownContainer(false)
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                                $digits = preg_replace('/\D+/', '', (string) $state);
                                                if ($digits === '') {
                                                    $set('staff_contact_number', '');

                                                    return;
                                                }

                                                $country = $get('staff_contact_number_country') ?: 'MY';
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

                                                $set('staff_contact_number', $digits);
                                            })
                                            ->dehydrateStateUsing(function (?string $state, Get $get): string {
                                                $digits = preg_replace('/\D+/', '', (string) $state);
                                                if ($digits === '') {
                                                    return '';
                                                }

                                                $country = $get('staff_contact_number_country') ?: 'MY';
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
                                            })
                                            ->columnSpan(1),

                                        TextInput::make('staff_email')
                                            ->label(__('client.form.staff_email'))
                                            ->email()
                                            ->maxLength(255)
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel(__('client.form.add_staff'))
                            ->addActionAlignment(Alignment::Start)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => ! empty($state['staff_name']) ? $state['staff_name'] : __('client.form.staff_placeholder'))
                            ->columnSpanFull(),

                    ])
                    ->collapsible(),

                Section::make(__('client.section.company_info'))
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                            '2xl' => 2,
                        ])->schema([

                            TextInput::make('company_name')
                                ->label(__('client.form.company_name'))
                                ->nullable()
                                ->extraAlpineAttributes(['x-ref' => 'companyName'])
                                ->helperText(__('client.form.company_name_helper'))
                                ->placeholder(fn (callable $get) => $get('pic_name')),

                            TextInput::make('company_email')->label(__('client.form.company_email'))
                                ->email()
                                ->nullable(),

                            Textarea::make('company_address')
                                ->label(__('client.form.company_address'))
                                ->rows(2)
                                ->nullable(),

                            Textarea::make('billing_address')
                                ->label(__('client.form.billing_address'))
                                ->rows(2)
                                ->nullable(),

                        ]),
                    ]),

                Section::make()
                    ->heading(__('client.section.extra_info'))
                    ->collapsible(true)
                    ->collapsed(fn ($get) => empty($get('notes')))
                    ->live()
                    ->schema([

                        RichEditor::make('notes')
                            ->label(__('client.form.notes'))
                            ->maxLength(500)
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
                            ->nullable(),

                        Repeater::make('extra_information')
                            ->label(__('client.form.extra_information'))
                            // ->relationship('extra_information')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('client.form.extra_title'))
                                            ->maxLength(100)
                                            ->debounce(1000)
                                            ->columnSpanFull(),
                                        RichEditor::make('value')
                                            ->label(__('client.form.extra_value'))
                                            ->maxLength(500)
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
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('client.form.add_extra_info'))
                            ->addActionAlignment(Alignment::Start)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('client.form.title_placeholder_short'))
                            ->live(onBlur: true)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),

                    ])
                    ->collapsible(),

                Section::make(__('client.section.status_info'))
                    ->schema(function (Get $get) {
                        // Check if we're in edit mode by looking for record in route
                        $recordId = request()->route('record');
                        $isEditMode = $recordId !== null;
                        $canEditVisibility = true;

                        if ($isEditMode) {
                            // We're editing - get the record from route
                            $record = Client::find($recordId);
                            $canEditVisibility = $record && $record->created_by === auth()->id();
                        }

                        if ($canEditVisibility) {
                            // User can edit visibility - show radio field
                            return [
                                \Filament\Forms\Components\Radio::make('visibility_status')
                                    ->label(__('client.form.status'))
                                    ->options([
                                        'active' => __('client.form.status_active'),
                                        'draft' => __('client.form.status_draft'),
                                    ])
                                    ->default('active')
                                    ->inline()
                                    ->required()
                                    ->helperText(__('client.form.status_helper')),
                            ];
                        } else {
                            // User cannot edit visibility - show message with clickable creator name
                            $creator = null;
                            if ($isEditMode && $record) {
                                $creator = $record->createdBy;
                            }

                            return [
                                \Filament\Forms\Components\Placeholder::make('visibility_status_readonly')
                                    ->label(__('client.form.status'))
                                    ->content(new HtmlString(
                                        __('client.form.status_helper_readonly').' '.
                                        \Blade::render('<x-clickable-creator-name :user="$user" />', ['user' => $creator]).
                                        '.'
                                    )),
                            ];
                        }
                    }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Disable record URL and record action for all records
            ->recordUrl(null)
            ->recordAction(null)
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['createdBy', 'updatedBy'])
                    ->withCount(['projects', 'importantUrls'])
                    ->visibleToUser()
            )
            ->columns([

                TextColumn::make('id')
                    ->label(__('client.table.id'))
                    ->url(fn ($record) => $record->trashed() ? null : route('filament.admin.resources.clients.edit', $record->id))
                    ->sortable(),

                Tables\Columns\ViewColumn::make('pic_name')
                    ->label(__('client.table.pic_name'))
                    ->view('filament.resources.client-resource.pic-name-column')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country_from_phone')
                    ->label(__('client.table.country'))
                    ->getStateUsing(function ($record) {
                        $phone = $record->pic_contact_number ?? '';
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
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('pic_contact_number')
                    ->label(__('client.table.pic_contact_number'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('project_count')
                    ->label(__('client.table.project_count'))
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('important_url_count')
                    ->label(__('client.table.important_url_count'))
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('visibility_status')
                    ->label(__('client.table.status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => __('client.table.status_active'),
                        'draft' => __('client.table.status_draft'),
                        default => $state,
                    })
                    ->toggleable()
                    ->visible(true)
                    ->alignment(Alignment::Center),

                TextColumn::make('created_at')
                    ->label(__('client.table.created_at_by'))
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
                    ->toggleable(),

                Tables\Columns\ViewColumn::make('updated_at')
                    ->label(__('client.table.updated_at_by'))
                    ->view('filament.resources.client-resource.updated-by-column')
                    ->sortable(),

            ])
            ->filters([

                Filter::make('country_code')
                    ->label(__('client.filter.country_code'))
                    ->form([
                        \Filament\Forms\Components\Select::make('country_code')
                            ->label(__('client.filter.country_code'))
                            ->multiple()
                            ->options(function () {
                                // Get distinct country codes from existing phone numbers
                                $phoneNumbers = Client::whereNotNull('pic_contact_number')
                                    ->where('pic_contact_number', '!=', '')
                                    ->pluck('pic_contact_number')
                                    ->unique();

                                $countryCodes = [];
                                $countryMapping = [
                                    '60' => 'Malaysia',
                                    '62' => 'Indonesia',
                                    '65' => 'Singapore',
                                ];

                                foreach ($phoneNumbers as $phone) {
                                    // Extract digits only (handles both +60 and 60 formats)
                                    $digitsOnly = preg_replace('/\D+/', '', $phone);

                                    if (empty($digitsOnly)) {
                                        continue;
                                    }

                                    // Try 2-digit country code first
                                    $firstTwo = substr($digitsOnly, 0, 2);
                                    if (isset($countryMapping[$firstTwo])) {
                                        $countryCode = $firstTwo;
                                        $countryName = $countryMapping[$firstTwo];
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
                                    $subQuery->where('pic_contact_number', 'like', "+{$code}%")
                                        ->orWhere('pic_contact_number', 'like', "{$code}%");
                                };

                                if ($index === 0) {
                                    $q->where($condition);
                                } else {
                                    $q->orWhere($condition);
                                }
                            }
                        });
                    }),

                Tables\Filters\SelectFilter::make('visibility_status')
                    ->label(__('client.table.status'))
                    ->options([
                        'active' => __('client.table.status_active'),
                        'draft' => __('client.table.status_draft'),
                    ])
                    ->preload()
                    ->searchable(),

                TrashedFilter::make()
                    ->label(__('client.filter.trashed'))
                    ->searchable(), // To show trashed or only active

            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\EditAction::make()->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('toggle_visibility_status')
                        ->label(fn ($record) => $record->visibility_status === 'active'
                            ? __('client.actions.make_draft')
                            : __('client.actions.make_active'))
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
                                ->title(__('client.actions.status_updated'))
                                ->body($newStatus === 'active'
                                    ? __('client.actions.client_activated')
                                    : __('client.actions.client_made_draft'))
                                ->success()
                                ->send();
                        })
                        ->tooltip(fn ($record) => $record->visibility_status === 'active'
                            ? __('client.actions.make_draft_tooltip')
                            : __('client.actions.make_active_tooltip'))
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Client Information Section (matches first section in form)
                Infolists\Components\Section::make(__('client.section.client_info'))
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('pic_name')
                                    ->label(__('client.form.pic_name')),
                                Infolists\Components\TextEntry::make('pic_contact_number')
                                    ->label(__('client.form.pic_contact_number')),
                                Infolists\Components\TextEntry::make('pic_email')
                                    ->label(__('client.form.pic_email'))
                                    ->placeholder(__('No email')),
                            ]),
                    ]),

                // Staff Information Section (matches second section in form)
                Infolists\Components\Section::make(__('client.section.staff_info'))
                    ->description(__('client.section.staff_info_description'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('staff_information')
                            ->label(__('client.form.staff_information'))
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('staff_name')
                                            ->label(__('client.form.staff_name')),
                                        Infolists\Components\TextEntry::make('staff_contact_number')
                                            ->label(__('client.form.staff_contact_number')),
                                        Infolists\Components\TextEntry::make('staff_email')
                                            ->label(__('client.form.staff_email'))
                                            ->placeholder(__('No email')),
                                    ]),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                // Company Information Section (matches third section in form)
                Infolists\Components\Section::make(__('client.section.company_info'))
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('company_name')
                                    ->label(__('client.form.company_name'))
                                    ->placeholder(__('No company name')),
                                Infolists\Components\TextEntry::make('company_email')
                                    ->label(__('client.form.company_email'))
                                    ->placeholder(__('No company email')),
                                Infolists\Components\TextEntry::make('company_address')
                                    ->label(__('client.form.company_address'))
                                    ->placeholder(__('No company address'))
                                    ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('billing_address')
                                    ->label(__('client.form.billing_address'))
                                    ->placeholder(__('No billing address'))
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // Additional Information Section (matches fourth section in form)
                Infolists\Components\Section::make()
                    ->heading(function ($record) {
                        $count = count($record->extra_information ?? []);

                        $title = __('client.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new HtmlString($title.' '.$badge);
                    })
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label(__('client.form.notes'))
                            ->markdown()
                            ->placeholder(__('No notes'))
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('extra_information')
                            ->label(__('client.form.extra_information'))
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label(__('client.form.extra_title')),
                                Infolists\Components\TextEntry::make('value')
                                    ->label(__('client.form.extra_value'))
                                    ->markdown(),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                // Status Information Section (matches fifth section in form)
                Infolists\Components\Section::make(__('client.section.status_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('visibility_status')
                            ->label(__('client.form.status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'draft' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => __('client.form.status_active'),
                                'draft' => __('client.form.status_draft'),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProjectsRelationManager::class,
            RelationManagers\ImportantUrlsRelationManager::class,
            ClientActivityLogRelationManager::class,
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
