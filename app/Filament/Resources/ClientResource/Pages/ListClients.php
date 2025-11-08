<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Str;
use Throwable;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_client')
                ->label(__('client.actions.create'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading(__('client.actions.create'))
                ->modalWidth('5xl')
                ->form([

                    Section::make(__('client.section.client_info'))
                        ->schema([

                            Grid::make([

                                'default' => 1,
                                'md' => 3,

                            ])
                                ->schema([

                                    TextInput::make('pic_name')
                                        ->label(__('client.form.pic_name'))
                                        ->required()
                                        ->maxLength(100)
                                        ->reactive()
                                        ->debounce(500)
                                        ->extraAttributes([
                                            'x-on:blur' => "
                                            if (\$refs.companyName && !\$refs.companyName.value) {
                                                \$refs.companyName.value = \$el.value;
                                                \$el.dispatchEvent(new Event('input'));
                                                \$refs.companyName.dispatchEvent(new Event('input'));
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

                        ]),

                    Section::make(__('client.section.company_info'))
                        ->schema([

                            Grid::make([

                                'default' => 1,
                                'md' => 2,

                            ])
                                ->schema([

                                    TextInput::make('company_name')
                                        ->label(__('client.form.company_name'))
                                        ->nullable()
                                        ->extraAlpineAttributes(['x-ref' => 'companyName'])
                                        ->helperText(__('client.form.company_name_helper'))
                                        ->placeholder(fn (callable $get) => $get('pic_name')),

                                    TextInput::make('company_email')
                                        ->label(__('client.form.company_email'))
                                        ->email()
                                        ->nullable(),

                                    Textarea::make('company_address')
                                        ->label(__('client.form.company_address'))
                                        ->rows(2)
                                        ->nullable()
                                        ->columnSpanFull(),

                                    Textarea::make('billing_address')
                                        ->label(__('client.form.billing_address'))
                                        ->rows(2)
                                        ->nullable()
                                        ->columnSpanFull(),

                                ]),

                        ]),

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
                                ->extraAttributes([
                                    'style' => 'resize: vertical;',
                                ])
                                ->helperText(function (Get $get) {
                                    $raw = $get('notes') ?? '';
                                    if (empty($raw)) {
                                        return __('client.form.notes_helper', ['count' => 500]);
                                    }

                                    $textOnly = strip_tags($raw);
                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                    return __('client.form.notes_helper', ['count' => $remaining]);
                                })
                                ->rule(function (Get $get): Closure {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if (empty($value)) {
                                            return;
                                        }

                                        $textOnly = strip_tags($value);
                                        $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                        $textOnly = trim(preg_replace('/\s+/', ' ', $textOnly));
                                        if (mb_strlen($textOnly) > 500) {
                                            $fail(__('client.form.notes_warning'));
                                        }
                                    };
                                })
                                ->nullable(),

                            Repeater::make('extra_information')
                                ->label(__('client.form.extra_information'))
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
                                                ->helperText(function (Get $get) {
                                                    $raw = $get('value') ?? '';
                                                    if (empty($raw)) {
                                                        return __('client.form.notes_helper', ['count' => 500]);
                                                    }

                                                    $textOnly = strip_tags($raw);
                                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                                    return __('client.form.notes_helper', ['count' => $remaining]);
                                                })
                                                ->rule(function (Get $get): Closure {
                                                    return function (string $attribute, $value, Closure $fail) {
                                                        if (empty($value)) {
                                                            return;
                                                        }

                                                        $textOnly = strip_tags($value);
                                                        $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                        $textOnly = trim(preg_replace('/\s+/', ' ', $textOnly));
                                                        if (mb_strlen($textOnly) > 500) {
                                                            $fail(__('client.form.notes_warning'));
                                                        }
                                                    };
                                                })
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
                                ->columnSpanFull(),

                        ]),

                ])
                ->modalSubmitActionLabel(__('client.actions.create'))
                ->action(function (array $data): void {
                    $this->createClient($data);
                }),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function createClient(array $data): void
    {
        try {
            $extraInformation = collect($data['extra_information'] ?? [])
                ->map(function (array $item) {
                    $title = trim((string) ($item['title'] ?? ''));
                    $value = $item['value'] ?? null;
                    $valueIsEmpty = blank(trim(strip_tags((string) $value)));

                    if ($title === '' && $valueIsEmpty) {
                        return null;
                    }

                    return [
                        'title' => $title,
                        'value' => $valueIsEmpty ? null : $value,
                    ];
                })
                ->filter()
                ->values()
                ->all();

            $staffInformation = collect($data['staff_information'] ?? [])
                ->map(function (array $item) {
                    $name = trim((string) ($item['staff_name'] ?? ''));
                    $phone = preg_replace('/\D+/', '', (string) ($item['staff_contact_number'] ?? ''));
                    $email = trim((string) ($item['staff_email'] ?? ''));

                    if ($name === '' && $phone === '' && $email === '') {
                        return null;
                    }

                    return [
                        'staff_name' => $name === '' ? null : $name,
                        'staff_contact_number' => $phone === '' ? null : $phone,
                        'staff_email' => $email === '' ? null : $email,
                    ];
                })
                ->filter()
                ->values()
                ->all();

            $payload = [
                'pic_name' => $data['pic_name'],
                'pic_contact_number' => $data['pic_contact_number'],
                'pic_email' => $data['pic_email'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'company_email' => $data['company_email'] ?? null,
                'company_address' => $data['company_address'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'extra_information' => $extraInformation,
                'staff_information' => $staffInformation,
                'updated_by' => auth()->id(),
            ];

            /**
             * @var Client $client
             */
            $client = Client::create($payload);

            Notification::make()
                ->title(__('client.actions.create'))
                ->body(__('client.form.pic_name').': '.$client->pic_name)
                ->success()
                ->send();

            $this->dispatch('$refresh');
        } catch (Throwable $exception) {
            Notification::make()
                ->title(__('client.actions.create'))
                ->body(Str::limit($exception->getMessage(), 200))
                ->danger()
                ->send();
        }
    }
}
