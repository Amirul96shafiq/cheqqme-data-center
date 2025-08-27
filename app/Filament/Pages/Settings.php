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

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        // API section
        Forms\Components\Section::make(__('settings.section.api'))
          ->description(__('settings.section.api_description'))
          ->schema([
            // Current API key
            Forms\Components\TextInput::make('current_api_key')
              ->label(__('settings.form.current_api_key'))
              ->disabled()
              ->dehydrated(false)
              ->placeholder(__('settings.form.no_api_key'))
              ->helperText(__('settings.form.api_key_helper')),

            Forms\Components\Actions::make([
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
              ->columnSpanFull(),

            // API documentation
            Forms\Components\Placeholder::make('api_documentation')
              ->label(__('settings.form.api_documentation'))
              ->content(new \Illuminate\Support\HtmlString(__('settings.form.api_documentation_content', [
                'base_url' => config('app.url') . '/api',
                'api_docs_url' => route('api.documentation', [], false),
              ]))),
          ])
          ->columns(1),
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
