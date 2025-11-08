<?php

namespace App\Filament\Resources\TrelloBoardResource\Pages;

use App\Filament\Resources\TrelloBoardResource;
use App\Models\TrelloBoard;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Str;
use Throwable;

class ListTrelloBoards extends ListRecords
{
    protected static string $resource = TrelloBoardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_trello_board')
                ->label(__('trelloboard.actions.create'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading(__('trelloboard.actions.create'))
                ->modalWidth('lg')
                ->form([

                    Section::make(__('trelloboard.section.trello_board_info'))
                        ->schema([

                            Grid::make(1)
                                ->schema([

                                    TextInput::make('url')
                                        ->label(__('trelloboard.form.board_url'))
                                        ->placeholder('https://trello.com/b/12345678/board-name')
                                        ->helperText(__('trelloboard.form.board_url_note'))
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (callable $set, ?string $state): void {
                                            if (empty($state)) {
                                                return;
                                            }

                                            $parsedUrl = parse_url($state);
                                            if (isset($parsedUrl['path'])) {
                                                $path = trim($parsedUrl['path'], '/');
                                                $pathParts = explode('/', $path);
                                                $boardName = end($pathParts);

                                                if (! empty($boardName)) {
                                                    $formattedName = ucwords(str_replace(['-', '_'], ' ', $boardName));
                                                    $set('name', $formattedName);
                                                }
                                            }
                                        })
                                        ->hintAction(
                                            fn (Get $get) => blank($get('url')) ? null : Action::make('openUrl')
                                                ->icon('heroicon-m-arrow-top-right-on-square')
                                                ->label(__('trelloboard.form.open_url'))
                                                ->url(fn () => $get('url'), true)
                                                ->tooltip(__('trelloboard.form.board_url_helper'))
                                        )
                                        ->url(),

                                    TextInput::make('name')
                                        ->label(__('trelloboard.form.board_name'))
                                        ->required()
                                        ->placeholder('Board Name')
                                        ->helperText(__('trelloboard.form.board_name_helper')),

                                ]),

                        ]),

                    Section::make(__('trelloboard.section.display_info'))
                        ->schema([

                            Toggle::make('show_on_boards')
                                ->label(__('trelloboard.form.show_on_boards'))
                                ->default(true),

                        ]),

                    Section::make(__('trelloboard.section.extra_info'))
                        ->schema([

                            RichEditor::make('notes')
                                ->label(__('trelloboard.form.trelloboard_notes'))
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
                                        return __('trelloboard.form.notes_helper', ['count' => 500]);
                                    }

                                    $textOnly = strip_tags($raw);
                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                    return __('trelloboard.form.notes_helper', ['count' => $remaining]);
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
                                            $fail(__('trelloboard.form.notes_warning'));
                                        }
                                    };
                                })
                                ->nullable(),

                            Repeater::make('extra_information')
                                ->label(__('trelloboard.form.extra_information'))
                                ->schema([

                                    Grid::make()
                                        ->schema([

                                            TextInput::make('title')
                                                ->label(__('trelloboard.form.extra_title'))
                                                ->maxLength(100)
                                                ->debounce(1000)
                                                ->columnSpanFull(),

                                            RichEditor::make('value')
                                                ->label(__('trelloboard.form.extra_value'))
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
                                                        return __('trelloboard.form.notes_helper', ['count' => 500]);
                                                    }

                                                    $textOnly = strip_tags($raw);
                                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                                    return __('trelloboard.form.notes_helper', ['count' => $remaining]);
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
                                                            $fail(__('trelloboard.form.notes_warning'));
                                                        }
                                                    };
                                                })
                                                ->nullable()
                                                ->columnSpanFull(),

                                        ]),

                                ])
                                ->columns(1)
                                ->defaultItems(1)
                                ->addActionLabel(__('trelloboard.form.add_extra_info'))
                                ->addActionAlignment(Alignment::Start)
                                ->cloneable()
                                ->reorderable()
                                ->collapsible(true)
                                ->collapsed()
                                ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('trelloboard.form.title_placeholder_short'))
                                ->columnSpanFull(),

                        ]),

                ])
                ->modalSubmitActionLabel(__('trelloboard.actions.create'))
                ->action(function (array $data): void {
                    $this->createTrelloBoard($data);
                }),
                
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function createTrelloBoard(array $data): void
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
                'url' => $data['url'],
                'name' => $data['name'],
                'show_on_boards' => (bool) ($data['show_on_boards'] ?? true),
                'notes' => $data['notes'] ?? null,
                'extra_information' => $extraInformation,
                'updated_by' => auth()->id(),
            ];

            $board = TrelloBoard::create($payload);

            Notification::make()
                ->title(__('trelloboard.actions.create'))
                ->body(__('trelloboard.form.board_name').': '.$board->name)
                ->success()
                ->send();

            $this->dispatch('$refresh');
        } catch (Throwable $exception) {
            Notification::make()
                ->title(__('trelloboard.actions.create'))
                ->body(Str::limit($exception->getMessage(), 200))
                ->danger()
                ->send();
        }
    }
}
