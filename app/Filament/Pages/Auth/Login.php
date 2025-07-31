<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Redirect;


class Login extends BaseLogin
{
    //protected static ?string $navigationIcon = 'heroicon-o-document-text';

    //protected static string $view = 'filament.pages.auth.login';

    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        //dd('Custom Login Loaded');
        parent::mount();

        $status = session()->pull('status');

        if ($status) {
            Notification::make()
                ->title($status)
                ->success()
                ->duration(5000)
                ->send();
        }
    }
}
