<?php

namespace App\Filament\Resources\ImportantUrlResource\Pages;

use App\Filament\Resources\ImportantUrlResource;
use App\Helpers\ClientFormatter;
use App\Models\ImportantUrl;
use App\Models\Project;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Throwable;

class ListImportantUrls extends ListRecords
{
    protected static string $resource = ImportantUrlResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('importanturl.tabs.all')),

            'today' => Tab::make(__('importanturl.tabs.today'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereBetween('created_at', [
                        now()->startOfDay(),
                        now()->endOfDay(),
                    ]);
                }),

            'this_week' => Tab::make(__('importanturl.tabs.this_week'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]);
                }),

            'this_month' => Tab::make(__('importanturl.tabs.this_month'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                }),

            'this_year' => Tab::make(__('importanturl.tabs.this_year'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereYear('created_at', now()->year);
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_important_url')
                ->label(__('importanturl.actions.create'))
                ->icon('heroicon-o-plus')
                ->modalHeading(__('importanturl.actions.create'))
                ->modalWidth('lg')
                ->color('primary')
                ->form([

                    Section::make(__('importanturl.section.important_url_info'))
                        ->schema([

                            Grid::make([
                                
                                'default' => 1,

                            ])->schema([

                                TextInput::make('title')
                                    ->label(__('importanturl.form.important_url_title'))
                                    ->required()
                                    ->maxLength(100),

                                Select::make('client_id')
                                    ->label(__('importanturl.form.client'))
                                    ->relationship('client', 'pic_name')
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        return ClientFormatter::formatClientDisplay($record->pic_name, $record->company_name);
                                    })
                                    ->preload()
                                    ->searchable()
                                    ->native(false)
                                    ->dehydrated()
                                    ->live()
                                    ->prefixAction(
                                        FormAction::make('openClient')
                                            ->icon('heroicon-o-pencil-square')
                                            ->url(function (Get $get) {
                                                $clientId = $get('client_id');
                                                if (! $clientId) {
                                                    return null;
                                                }

                                                return \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $clientId]);
                                            })
                                            ->openUrlInNewTab()
                                            ->visible(fn (Get $get) => (bool) $get('client_id'))
                                    )
                                    ->suffixAction(
                                        FormAction::make('createClient')
                                            ->icon('heroicon-o-plus')
                                            ->url(\App\Filament\Resources\ClientResource::getUrl('create'))
                                            ->openUrlInNewTab()
                                            ->label(__('importanturl.form.create_client'))
                                    )
                                    ->nullable(),

                                Select::make('project_id')
                                    ->label(__('importanturl.form.project'))
                                    ->options(function () {
                                        return Project::all()->mapWithKeys(function ($project) {
                                            $truncatedTitle = strlen($project->title) > 25
                                                ? substr($project->title, 0, 25).'...'
                                                : $project->title;

                                            return [$project->id => $truncatedTitle];
                                        });
                                    })
                                    ->preload()
                                    ->searchable()
                                    ->native(false)
                                    ->dehydrated()
                                    ->live()
                                    ->prefixAction(
                                        FormAction::make('openProject')
                                            ->icon('heroicon-o-pencil-square')
                                            ->url(function (Get $get) {
                                                $projectId = $get('project_id');
                                                if (! $projectId) {
                                                    return null;
                                                }

                                                return \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $projectId]);
                                            })
                                            ->openUrlInNewTab()
                                            ->visible(fn (Get $get) => (bool) $get('project_id'))
                                    )
                                    ->suffixAction(
                                        FormAction::make('createProject')
                                            ->icon('heroicon-o-plus')
                                            ->url(\App\Filament\Resources\ProjectResource::getUrl('create'))
                                            ->openUrlInNewTab()
                                            ->label(__('importanturl.form.create_project'))
                                    )
                                    ->nullable(),

                            ]),

                            TextInput::make('url')
                                ->label(__('importanturl.form.important_url'))
                                ->helperText(__('importanturl.form.important_url_note'))
                                ->required()
                                ->hintAction(
                                    fn (Get $get) => blank($get('url')) ? null : FormAction::make('openUrl')
                                        ->icon('heroicon-m-arrow-top-right-on-square')
                                        ->label(__('importanturl.form.open_url'))
                                        ->url(fn () => $get('url'), true)
                                        ->tooltip(__('importanturl.form.important_url_helper'))
                                )
                                ->url(),

                        ]),

                    Section::make(__('importanturl.section.extra_info'))
                        ->schema([

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
                                ->extraAttributes([
                                    'style' => 'resize: vertical;',
                                ])
                                ->helperText(function (Get $get) {
                                    $raw = $get('notes') ?? '';
                                    if (empty($raw)) {
                                        return __('importanturl.form.notes_helper', ['count' => 500]);
                                    }

                                    $textOnly = strip_tags($raw);
                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                    return __('importanturl.form.notes_helper', ['count' => $remaining]);
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
                                            $fail(__('importanturl.form.notes_warning'));
                                        }
                                    };
                                })
                                ->nullable(),

                            Repeater::make('extra_information')
                                ->label(__('importanturl.form.extra_information'))
                                ->schema([

                                    Grid::make()
                                        ->schema([

                                            TextInput::make('title')
                                                ->label(__('importanturl.form.extra_title'))
                                                ->maxLength(100)
                                                ->debounce(1000)
                                                ->columnSpanFull(),

                                            RichEditor::make('value')
                                                ->label(__('importanturl.form.extra_value'))
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
                                                        return __('importanturl.form.notes_helper', ['count' => 500]);
                                                    }

                                                    $textOnly = strip_tags($raw);
                                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                                    return __('importanturl.form.notes_helper', ['count' => $remaining]);
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
                                                            $fail(__('importanturl.form.notes_warning'));
                                                        }
                                                    };
                                                })
                                                ->nullable()
                                                ->columnSpanFull(),

                                        ]),

                                ])
                                ->columns(1)
                                ->defaultItems(1)
                                ->addActionLabel(__('importanturl.form.add_extra_info'))
                                ->addActionAlignment(Alignment::Start)
                                ->cloneable()
                                ->reorderable()
                                ->collapsible(true)
                                ->collapsed()
                                ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('importanturl.form.title_placeholder_short'))
                                ->columnSpanFull(),

                        ]),
                ])
                ->modalSubmitActionLabel(__('importanturl.actions.create'))
                ->action(function (array $data): void {
                    $this->createImportantUrl($data);
                }),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function createImportantUrl(array $data): void
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
                'url' => $data['url'],
                'client_id' => $data['client_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'extra_information' => $extraInformation,
                'updated_by' => auth()->id(),
            ];

            $importantUrl = ImportantUrl::create($payload);

            Notification::make()
                ->title(__('importanturl.actions.create'))
                ->body(__('importanturl.form.important_url_title').': '.$importantUrl->title)
                ->success()
                ->send();

            $this->dispatch('$refresh');
        } catch (Throwable $exception) {
            Notification::make()
                ->title(__('importanturl.actions.create'))
                ->body(Str::limit($exception->getMessage(), 200))
                ->danger()
                ->send();
        }
    }
}
