<?php

namespace App\Filament\Http\Controllers\Auth;

use Filament\Http\Controllers\Auth\AuthenticatedSessionController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends BaseController
{
    protected function redirectTo(): string
    {
        return route('filament.admin.pages.dashboard');
    }
}
