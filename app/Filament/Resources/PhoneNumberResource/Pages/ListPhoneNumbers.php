<?php

namespace App\Filament\Resources\PhoneNumberResource\Pages;

use App\Filament\Resources\PhoneNumberResource;
use App\Models\PhoneNumber;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Str;
use Throwable;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ListPhoneNumbers extends ListRecords
{
    protected static string $resource = PhoneNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_phone_number')
                ->label(__('phonenumber.actions.create'))
                ->icon('heroicon-o-plus')
                ->modalHeading(__('phonenumber.actions.create'))
                ->modalSubmitActionLabel(__('phonenumber.actions.create'))
                ->modalWidth('lg')
                ->form([

                    Section::make(__('phonenumber.section.phone_number_info'))
                        ->schema([

                            TextInput::make('title')
                                ->label(__('phonenumber.form.phone_number_title'))
                                ->required()
                                ->maxLength(100),

                            PhoneInput::make('phone')
                                ->label(__('phonenumber.form.phone_number'))
                                ->required()
                                ->countryStatePath('phone_country')
                                ->initialCountry('MY')
                                ->countryOrder(['MY', 'ID', 'SG', 'PH', 'US'])
                                ->onlyCountries(['MY', 'ID', 'SG', 'PH', 'US'])
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

                        ]),

                    Section::make()
                        ->heading(__('phonenumber.section.extra_info'))
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
                                ->extraAttributes([
                                    'style' => 'resize: vertical;',
                                ])
                                ->helperText(function (Get $get) {
                                    $raw = $get('notes') ?? '';
                                    if (empty($raw)) {
                                        return __('phonenumber.form.notes_helper', ['count' => 500]);
                                    }

                                    $textOnly = strip_tags($raw);
                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                    return __('phonenumber.form.notes_helper', ['count' => $remaining]);
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
                                            $fail(__('phonenumber.form.notes_warning'));
                                        }
                                    };
                                })
                                ->nullable(),

                            Repeater::make('extra_information')
                                ->label(__('phonenumber.form.extra_information'))
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
                                                ->helperText(function (Get $get) {
                                                    $raw = $get('value') ?? '';
                                                    if (empty($raw)) {
                                                        return __('phonenumber.form.notes_helper', ['count' => 500]);
                                                    }

                                                    $textOnly = strip_tags($raw);
                                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                                    return __('phonenumber.form.notes_helper', ['count' => $remaining]);
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
                                ->columnSpanFull(),

                        ]),

                ])
                ->action(function (array $data): void {
                    $this->createPhoneNumber($data);
                }),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function createPhoneNumber(array $data): void
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

            $payload = [
                'title' => $data['title'],
                'phone' => $data['phone'],
                'notes' => $data['notes'] ?? null,
                'extra_information' => $extraInformation,
                'updated_by' => auth()->id(),
            ];

            $phoneNumber = PhoneNumber::create($payload);

            Notification::make()
                ->title(__('phonenumber.actions.create'))
                ->body(__('phonenumber.form.phone_number_title').': '.$phoneNumber->title)
                ->success()
                ->send();

            $this->dispatch('$refresh');
        } catch (Throwable $exception) {
            Notification::make()
                ->title(__('phonenumber.actions.create'))
                ->body(Str::limit($exception->getMessage(), 200))
                ->danger()
                ->send();
        }
    }
}
