<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use App\Models\Project;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Throwable;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('document.tabs.all')),

            'today' => Tab::make(__('document.tabs.today'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereBetween('created_at', [
                        now()->startOfDay(),
                        now()->endOfDay(),
                    ]);
                }),

            'this_week' => Tab::make(__('document.tabs.this_week'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]);
                }),

            'this_month' => Tab::make(__('document.tabs.this_month'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                }),

            'this_year' => Tab::make(__('document.tabs.this_year'))
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereYear('created_at', now()->year);
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_document')
                ->label(__('document.actions.create'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading(__('document.actions.create'))
                ->modalWidth('lg')
                ->form([

                    View::make('livewire.document-upload-handler'),
                    Section::make(__('document.section.document_info'))
                        ->schema([

                            Grid::make([

                                'default' => 1,

                            ])
                                ->schema([

                                    TextInput::make('title')
                                        ->label(__('document.form.document_title'))
                                        ->required()
                                        ->maxLength(100)
                                        ->columnSpanFull(),

                                    Select::make('project_id')
                                        ->label(__('document.form.project'))
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
                                                ->label(__('document.form.create_project'))
                                        )
                                        ->nullable()
                                        ->columnSpanFull(),

                                    Select::make('type')
                                        ->label(__('document.form.document_type'))
                                        ->options([
                                            'external' => __('document.form.external'),
                                            'internal' => __('document.form.internal'),
                                        ])
                                        ->searchable()
                                        ->default('internal')
                                        ->live()
                                        ->required()
                                        ->columnSpanFull(),

                                ]),

                            TextInput::make('url')
                                ->label(__('document.form.document_url'))
                                ->helperText(__('document.form.document_url_note'))
                                ->visible(fn (Get $get) => $get('type') === 'external')
                                ->required(fn (Get $get) => $get('type') === 'external')
                                ->hintAction(
                                    fn (Get $get) => blank($get('url')) ? null : FormAction::make('openUrl')
                                        ->icon('heroicon-m-arrow-top-right-on-square')
                                        ->label(__('document.form.open_url'))
                                        ->url(fn () => $get('url'), true)
                                        ->tooltip(__('document.form.document_url_helper'))
                                )
                                ->url()
                                ->nullable(),

                            FileUpload::make('file_path')
                                ->label(__('document.form.document_upload'))
                                ->helperText(__('document.form.document_upload_helper'))
                                ->visible(fn (Get $get) => $get('type') === 'internal')
                                ->required(fn (Get $get) => $get('type') === 'internal')
                                ->directory('documents')
                                ->disk('public')
                                ->visibility('public')
                                ->preserveFilenames()
                                ->enableDownload()
                                ->enableOpen()
                                ->deletable()
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'text/csv',
                                    'application/vnd.ms-powerpoint',
                                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                    'video/mp4',
                                ])
                                ->maxFiles(20480)
                                ->nullable(),

                        ]),

                    Section::make(__('document.section.extra_info'))
                        ->schema([

                            RichEditor::make('notes')
                                ->label(__('document.form.notes'))
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
                                        return __('document.form.notes_helper', ['count' => 500]);
                                    }

                                    $textOnly = strip_tags($raw);
                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                    return __('document.form.notes_helper', ['count' => $remaining]);
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
                                            $fail(__('document.form.notes_warning'));
                                        }
                                    };
                                })
                                ->nullable(),

                            Repeater::make('extra_information')
                                ->label(__('document.form.extra_information'))
                                ->schema([

                                    Grid::make()
                                        ->schema([

                                            TextInput::make('title')
                                                ->label(__('document.form.extra_title'))
                                                ->maxLength(100)
                                                ->debounce(1000)
                                                ->columnSpanFull(),

                                            RichEditor::make('value')
                                                ->label(__('document.form.extra_value'))
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
                                                        return __('document.form.notes_helper', ['count' => 500]);
                                                    }

                                                    $textOnly = strip_tags($raw);
                                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $remaining = max(0, 500 - mb_strlen($textOnly));

                                                    return __('document.form.notes_helper', ['count' => $remaining]);
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
                                                            $fail(__('document.form.notes_warning'));
                                                        }
                                                    };
                                                })
                                                ->nullable()
                                                ->columnSpanFull(),

                                        ]),

                                ])
                                ->columns(1)
                                ->defaultItems(1)
                                ->addActionLabel(__('document.form.add_extra_info'))
                                ->addActionAlignment(Alignment::Start)
                                ->cloneable()
                                ->reorderable()
                                ->collapsible(true)
                                ->collapsed()
                                ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('document.form.title_placeholder_short'))
                                ->columnSpanFull(),

                        ]),

                ])
                ->modalSubmitActionLabel(__('document.actions.create'))
                ->action(function (array $data): void {
                    $this->createDocument($data);
                }),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function createDocument(array $data): void
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

            $filePath = $data['file_path'] ?? null;
            if (is_array($filePath)) {
                $filePath = $filePath[0] ?? null;
            }

            $type = $data['type'] ?? 'internal';

            $payload = [
                'title' => $data['title'],
                'type' => $type,
                'project_id' => $data['project_id'] ?? null,
                'url' => $type === 'external' ? ($data['url'] ?? null) : null,
                'file_path' => $type === 'internal' ? $filePath : null,
                'notes' => $data['notes'] ?? null,
                'extra_information' => $extraInformation,
                'updated_by' => auth()->id(),
            ];

            $document = Document::create($payload);

            Notification::make()
                ->title(__('document.actions.create'))
                ->body(__('document.form.document_title').': '.$document->title)
                ->success()
                ->send();

            $this->dispatch('$refresh');
        } catch (Throwable $exception) {
            Notification::make()
                ->title(__('document.actions.create'))
                ->body(Str::limit($exception->getMessage(), 200))
                ->danger()
                ->send();
        }
    }
}
