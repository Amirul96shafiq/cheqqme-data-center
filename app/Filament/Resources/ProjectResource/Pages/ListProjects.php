<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Helpers\ClientFormatter;
use App\Models\Project;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Str;
use Throwable;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_project')
                ->label(__('project.actions.create'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading(__('project.actions.create'))
                ->modalWidth('lg')
                ->form([
                    Section::make(__('project.section.project_info'))
                        ->schema([

                            Grid::make([

                                'default' => 1,

                            ])
                                ->schema([

                                    TextInput::make('title')
                                        ->label(__('project.form.project_title'))
                                        ->required()
                                        ->maxLength(100)
                                        ->columnSpan(1),

                                    Select::make('client_id')
                                        ->label(__('project.form.client'))
                                        ->relationship('client', 'pic_name')
                                        ->getOptionLabelFromRecordUsing(function ($record) {
                                            return ClientFormatter::formatClientDisplay($record->pic_name, $record->company_name);
                                        })
                                        ->searchable()
                                        ->preload()
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
                                                ->label(__('project.form.create_client'))
                                        )
                                        ->nullable()
                                        ->columnSpan(1),

                                    Select::make('status')
                                        ->label(__('project.form.project_status'))
                                        ->options([
                                            'Planning' => __('project.form.planning'),
                                            'In Progress' => __('project.form.in_progress'),
                                            'Completed' => __('project.form.completed'),
                                        ])
                                        ->searchable()
                                        ->nullable()
                                        ->columnSpan(1),

                                ]),

                            TextInput::make('project_url')
                                ->label(__('project.form.project_url'))
                                ->url()
                                ->nullable(),

                            Textarea::make('description')
                                ->label(__('project.form.project_description'))
                                ->rows(3)
                                ->nullable()
                                ->maxLength(200),

                        ]),

                    Section::make(__('project.section.extra_info'))
                        ->schema([

                            RichEditor::make('notes')
                                ->label(__('project.form.notes'))
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
                                        return __('project.form.notes_helper', ['count' => 500]);
                                    }

                                    $textOnly = strip_tags($raw);
                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                    return __('project.form.notes_helper', ['count' => $remaining]);
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
                                            $fail(__('project.form.notes_warning'));
                                        }
                                    };
                                })
                                ->nullable(),

                            Repeater::make('extra_information')
                                ->label(__('project.form.extra_information'))
                                ->schema([

                                    Grid::make()
                                        ->schema([

                                            TextInput::make('title')
                                                ->label(__('project.form.extra_title'))
                                                ->maxLength(100)
                                                ->debounce(1000)
                                                ->columnSpanFull(),

                                            RichEditor::make('value')
                                                ->label(__('project.form.extra_value'))
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
                                                        return __('project.form.notes_helper', ['count' => 500]);
                                                    }

                                                    $textOnly = strip_tags($raw);
                                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                                    return __('project.form.notes_helper', ['count' => $remaining]);
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
                                                            $fail(__('project.form.notes_warning'));
                                                        }
                                                    };
                                                })
                                                ->nullable()
                                                ->columnSpanFull(),

                                        ]),

                                ])
                                ->columns(1)
                                ->defaultItems(1)
                                ->addActionLabel(__('project.form.add_extra_info'))
                                ->addActionAlignment(Alignment::Start)
                                ->cloneable()
                                ->reorderable()
                                ->collapsible(true)
                                ->collapsed()
                                ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('project.form.title_placeholder_short'))
                                ->columnSpanFull(),

                        ]),
                        
                ])
                ->modalSubmitActionLabel(__('project.actions.create'))
                ->action(function (array $data): void {
                    $this->createProject($data);
                }),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function createProject(array $data): void
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
                'client_id' => $data['client_id'] ?? null,
                'status' => $data['status'] ?? null,
                'project_url' => $data['project_url'] ?? null,
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
                'extra_information' => $extraInformation,
                'updated_by' => auth()->id(),
            ];

            $project = Project::create($payload);

            Notification::make()
                ->title(__('project.actions.create'))
                ->body(__('project.form.project_title').': '.$project->title)
                ->success()
                ->send();

            $this->dispatch('$refresh');
        } catch (Throwable $exception) {
            Notification::make()
                ->title(__('project.actions.create'))
                ->body(Str::limit($exception->getMessage(), 200))
                ->danger()
                ->send();
        }
    }
}
