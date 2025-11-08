<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Unique;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_user')
                ->label(__('user.actions.create'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    Section::make(__('user.section.user_info'))
                        ->schema([
                            TextInput::make('username')
                                ->label(__('user.form.username'))
                                ->required()
                                ->maxLength(20)
                                ->unique(table: 'users', column: 'username')
                                ->reactive()
                                ->debounce(500),

                            TextInput::make('name')
                                ->label(__('user.form.name'))
                                ->nullable()
                                ->helperText(__('user.form.name_helper'))
                                ->placeholder(fn (callable $get) => $get('username'))
                                ->maxLength(50)
                                ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null),

                            TextInput::make('email')
                                ->label(__('user.form.email'))
                                ->required()
                                ->email()
                                ->maxLength(60)
                                ->unique(
                                    table: 'users',
                                    column: 'email',
                                    modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')
                                ),
                        ]),

                    Section::make(__('user.section.password_info'))
                        ->schema([
                            FormActions::make([
                                FormAction::make('generatePassword')
                                    ->label(__('user.form.generate_password'))
                                    ->icon('heroicon-o-code-bracket-square')
                                    ->color('gray')
                                    ->action(function ($set) {
                                        $generated = str()->random(16);
                                        $set('password', $generated);
                                        $set('password_confirmation', $generated);
                                    }),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make('password')
                                    ->label(__('user.form.new_password'))
                                    ->helperText(__('user.form.password_helper'))
                                    ->password()
                                    ->revealable()
                                    ->minLength(5)
                                    ->required()
                                    ->same('password_confirmation'),

                                TextInput::make('password_confirmation')
                                    ->label(__('user.form.confirm_new_password'))
                                    ->password()
                                    ->revealable()
                                    ->required(),
                            ]),
                        ]),
                ])
                ->modalHeading(__('user.modal.create_heading'))
                ->modalSubmitActionLabel(__('user.actions.create'))
                ->modalWidth('lg')
                ->action(function (array $data) {
                    $this->createUser($data);
                }),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function createUser(array $data): void
    {
        try {
            // Hash the password
            $data['password'] = Hash::make($data['password']);
            $data['updated_by'] = auth()->id();

            // Set default online status to invisible
            $data['online_status'] = 'invisible';

            // Set name to username if empty
            if (empty($data['name'])) {
                $data['name'] = $data['username'];
            }

            // Remove password_confirmation from data
            unset($data['password_confirmation']);

            // Create the user
            $user = User::create($data);

            Notification::make()
                ->title(__('user.notifications.created'))
                ->body(__('user.notifications.created_body', ['name' => $user->username]))
                ->success()
                ->send();

            // Refresh the table
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('user.notifications.create_failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
