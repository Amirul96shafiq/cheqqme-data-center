<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Settings extends Page
{
  protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

  protected static string $view = 'filament.pages.settings';

  protected static ?string $navigationLabel = 'Settings';

  protected static ?string $title = 'Settings';

  protected static ?string $slug = 'settings';

  protected static ?int $navigationSort = 99;

  public ?array $data = [];

  public function mount(): void
  {
    $user = Auth::user();
    $data = $user->toArray();

    // Set the current API key display value
    $data['current_api_key'] = $user->getMaskedApiKey();

    $this->form->fill($data);
  }

  public function getListeners(): array
  {
    return [
      'clipboard-copy-success' => 'handleClipboardCopySuccess',
      'clipboard-copy-failure' => 'handleClipboardCopyFailure',
    ];
  }

  public function handleClipboardCopySuccess(): void
  {
    Notification::make()
      ->title(__('settings.form.api_key_copied'))
      ->body(__('settings.form.api_key_copied_body'))
      ->success()
      ->send();
  }

  public function handleClipboardCopyFailure(): void
  {
    Notification::make()
      ->title(__('settings.form.api_key_copy_failed'))
      ->body(__('settings.form.api_key_copy_failed_body'))
      ->danger()
      ->send();
  }

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        // API section
        Forms\Components\Section::make(__('settings.section.api'))
          ->description(__('settings.section.api_description'))
          ->schema([
            // Current API key row
            Forms\Components\Grid::make(12)
              ->schema([
                Forms\Components\Placeholder::make('current_api_key_label')
                  ->label(__('settings.form.current_api_key'))
                  ->content('')
                  ->columnSpan(4),

                Forms\Components\Grid::make(8)
                  ->schema([
                    Forms\Components\TextInput::make('current_api_key')
                      ->label('')
                      ->disabled()
                      ->dehydrated(false)
                      ->placeholder(__('settings.form.no_api_key'))
                      ->helperText(__('settings.form.api_key_helper'))
                      ->columnSpan(8),
                  ])
                  ->columnSpan(8),
              ]),

            // Actions row
            Forms\Components\Grid::make(12)
              ->schema([
                // Actions label
                Forms\Components\Placeholder::make('actions_label')
                  ->label('')
                  ->columnSpan(4),

                // Actions: Generate, Copy, Regenerate, Delete
                Forms\Components\Actions::make([
                  // Copy API key
                  \Filament\Forms\Components\Actions\Action::make('copy_api_key')
                    ->label(__('settings.form.copy_api_key'))
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->visible(fn() => auth()->user()->hasApiKey())
                    ->action(function () {
                      $user = auth()->user();

                      // Dispatch browser event to copy API key to clipboard
                      $this->dispatch('copy-api-key', apiKey: $user->api_key);

                      // Show notification that copy operation was initiated
                      Notification::make()
                        ->title(__('settings.form.api_key_copying'))
                        ->body(__('settings.form.api_key_copying_body'))
                        ->info()
                        ->send();
                    }),

                  // Generate API key
                  \Filament\Forms\Components\Actions\Action::make('generate_api_key')
                    ->label(__('settings.form.generate_api_key'))
                    ->icon('heroicon-o-key')
                    ->color('gray')
                    ->visible(fn() => !auth()->user()->hasApiKey())
                    ->action(function ($set) {
                      $user = auth()->user();
                      $apiKey = $user->generateApiKey();
                      $set('current_api_key', $user->getMaskedApiKey());

                      // Notification
                      Notification::make()
                        ->title(__('settings.form.api_key_generated'))
                        ->body(__('settings.form.api_key_generated_body'))
                        ->success()
                        ->send();
                    }),

                  // Regenerate API key
                  \Filament\Forms\Components\Actions\Action::make('regenerate_api_key')
                    ->label(__('settings.form.regenerate_api_key'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn() => auth()->user()->hasApiKey())
                    ->requiresConfirmation()
                    ->modalHeading(__('settings.form.confirm_regenerate'))
                    ->modalDescription(__('settings.form.confirm_regenerate_description'))
                    ->modalSubmitActionLabel(__('settings.form.regenerate'))
                    ->action(function ($set) {
                      $user = auth()->user();
                      $apiKey = $user->generateApiKey();
                      $set('current_api_key', $user->getMaskedApiKey());

                      Notification::make()
                        ->title(__('settings.form.api_key_regenerated'))
                        ->body(__('settings.form.api_key_regenerated_body'))
                        ->warning()
                        ->send();
                    }),

                  // Delete API key
                  \Filament\Forms\Components\Actions\Action::make('delete_api_key')
                    ->label(__('settings.form.delete_api_key'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->outlined()
                    ->visible(fn() => auth()->user()->hasApiKey())
                    ->requiresConfirmation()
                    ->modalHeading(__('settings.form.confirm_delete'))
                    ->modalDescription(__('settings.form.confirm_delete_description'))
                    ->modalSubmitActionLabel(__('settings.form.delete'))
                    ->action(function ($set) {
                      $user = auth()->user();
                      $user->update([
                        'api_key' => null,
                        'api_key_generated_at' => null,
                      ]);
                      $set('current_api_key', '');

                      Notification::make()
                        ->title(__('settings.form.api_key_deleted'))
                        ->body(__('settings.form.api_key_deleted_body'))
                        ->success()
                        ->send();
                    }),
                ])
                  ->columns(3)
                  ->columnSpan(8),
              ]),

            // API documentation row
            Forms\Components\Grid::make(12)
              ->schema([
                Forms\Components\Placeholder::make('api_documentation_label')
                  ->label(__('settings.form.api_documentation'))
                  ->content('')
                  ->columnSpan(4),

                Forms\Components\Placeholder::make('api_documentation')
                  ->label('')
                  ->content(new \Illuminate\Support\HtmlString(__('settings.form.api_documentation_content', [
                    'base_url' => config('app.url') . '/api',
                    'api_docs_url' => route('api.documentation', [], false),
                  ])))
                  ->columnSpan(8),
              ]),
          ]),
      ])
      ->statePath('data');
  }

  public function save(): void
  {
    $data = $this->form->getState();

    // Here you would typically save the settings to the user model or a settings table
    // For now, we'll just show a success notification
    // Auth::user()->update($data);

    Notification::make()
      ->title(__('settings.form.saved'))
      ->body(__('settings.form.saved_body'))
      ->success()
      ->send();
  }
}
