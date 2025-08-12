<?php

namespace App\Filament\Resources;

use App\Models\Task;
use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Pages\ActionBoard;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
  /*public static function shouldRegisterNavigation(): bool
  {
      return false;
  }*/
{
  /**
   * Redirect global search result to Action Board and open the selected Task.
   */
  public static function getGlobalSearchResultUrl($record): string
  {
    // Redirect to Task edit page
    return static::getUrl('edit', ['record' => $record->getKey()]);
  }
  protected static ?string $model = Task::class;

  public static function shouldRegisterNavigation(): bool
  {
    return false;
  }
  protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
  protected static ?string $navigationGroup = null; // Hide from navigation
  protected static ?string $recordTitleAttribute = 'title';


  public static function getGloballySearchableAttributes(): array
  {
    return ['title', 'description'];
  }

  /**
   * Redirect global search result to Action Board and open the selected Task.
   */
  /*
  public static function getGlobalSearchResultUrl($record): string
  {
      // Replace with your Action Board route and pass the Task ID as a query param
      return route('filament.pages.action-board', ['task' => $record->id]);
  }*/

  public static function getGlobalSearchResultDetails($record): array
  {
    return [
      __('task.search.status') => $record->status,
      __('task.search.due_date') => $record->due_date,
      __('task.search.assigned_to') => $record->assigned_to_username,
    ];
  }

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Grid::make(5)
        ->schema([
          // Main content (left side) - spans 2 columns
          Forms\Components\Grid::make(1)
            ->schema([
              Forms\Components\Section::make('Task Information')
                ->schema([
                  Forms\Components\Hidden::make('id')
                    ->disabled()
                    ->visible(false),
                  Forms\Components\TextInput::make('title')
                    ->required()
                    ->placeholder('Enter task title'),
                  Forms\Components\Grid::make(3)
                    ->schema([
                      Forms\Components\Select::make('assigned_to')
                        ->label('Assign To')
                        ->options(function () {
                          return \App\Models\User::withTrashed()
                            ->orderBy('username')
                            ->get()
                            ->mapWithKeys(fn($u) => [
                              $u->id => ($u->username ?: 'User #' . $u->id) . ($u->deleted_at ? ' (deleted)' : ''),
                            ])
                            ->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->nullable()
                        ->formatStateUsing(fn($state, ?Task $record) => $record?->assigned_to)
                        ->default(fn(?Task $record) => $record?->assigned_to)
                        ->dehydrated(),
                      Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date'),
                      Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                          'todo' => 'To Do',
                          'in_progress' => 'In Progress',
                          'toreview' => 'To Review',
                          'completed' => 'Completed',
                          'archived' => 'Archived',
                        ])
                        ->searchable(),
                    ]),
                  Forms\Components\RichEditor::make('description')
                    ->label('Description')
                    ->toolbarButtons([
                      'bold',
                      'italic',
                      'strike',
                      'bulletList',
                      'orderedList',
                      'link',
                      'codeBlock'
                    ])
                    ->extraAttributes(['style' => 'resize: vertical;'])
                    ->reactive()
                    ->helperText(function (Forms\Get $get) {
                      $raw = $get('description') ?? '';
                      $noHtml = strip_tags($raw);
                      $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                      $remaining = 500 - mb_strlen($decoded);
                      return __("action.edit.description_helper", ['count' => $remaining]);
                    })
                    ->rule(function (Forms\Get $get): \Closure {
                      return function (string $attribute, $value, \Closure $fail) {
                        $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                        if (mb_strlen($textOnly) > 500) {
                          $fail(__("action.edit.description_warning"));
                        }
                      };
                    })
                    ->nullable()
                    ->columnSpanFull(),
                ]),
              Forms\Components\Section::make('Task Additional Information')
                ->schema([
                  Forms\Components\Repeater::make('extra_information')
                    ->label('Extra Information')
                    ->schema([
                      Forms\Components\TextInput::make('title')
                        ->label('Title')
                        ->maxLength(100)
                        ->columnSpanFull(),
                      Forms\Components\RichEditor::make('value')
                        ->label(__('Value'))
                        ->toolbarButtons(['codeBlock'])
                        ->extraAttributes(['style' => 'resize: vertical;'])
                        ->reactive()
                        ->helperText(function (Forms\Get $get) {
                          $raw = $get('value') ?? '';
                          $noHtml = strip_tags($raw);
                          $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                          $remaining = 500 - mb_strlen($decoded);
                          return __("action.edit.extra_information_helper", ['count' => $remaining]);
                        })
                        ->rule(function (Forms\Get $get): \Closure {
                          return function (string $attribute, $value, \Closure $fail) {
                            $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                            if (mb_strlen($textOnly) > 500) {
                              $fail(__("action.edit.extra_information_warning"));
                            }
                          };
                        })
                        ->columnSpanFull(),
                    ])
                    ->defaultItems(1)
                    ->addActionLabel(__('client.form.add_extra_info'))
                    ->cloneable()
                    ->reorderable()
                    ->collapsible(true)
                    ->collapsed()
                    ->itemLabel(fn(array $state): string => !empty($state['title']) ? $state['title'] : 'Title goes here')
                    ->live()
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'no-repeater-collapse-toolbar'])
                ])
                ->collapsible()
                ->collapsed(),
            ])
            ->columnSpan(3),

          // Comments sidebar (right side) - spans 1 column
          Forms\Components\Section::make('Comments')
            ->schema([
              Forms\Components\ViewField::make('task_comments')
                ->view('filament.components.comments-sidebar-livewire-wrapper')
                ->viewData(function ($get, $record) {
                  return ['taskId' => $record instanceof Task ? $record->id : null];
                })
                ->extraAttributes([
                  'class' => 'flex-1 flex flex-col min-h-0',
                  'style' => 'height:100%; display:flex; flex-direction:column;'
                ])
                ->dehydrated(false),
            ])
            ->extraAttributes([
              'style' => 'height:68vh; max-height:68vh; position:sticky; top:3vh; display:flex; flex-direction:column; align-self:flex-start; overflow:hidden;',
              'class' => 'comments-pane'
            ])
            ->columnSpan(2),
        ])
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('title')->searchable(),
        Tables\Columns\TextColumn::make('status')->searchable(),
        Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
      ])
      ->filters([
        TrashedFilter::make(),
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
      ->defaultSort('order_column');
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListTasks::route('/'),
      'create' => Pages\CreateTask::route('/create'),
      'edit' => Pages\EditTask::route('/{record}/edit'),
    ];
  }
}
