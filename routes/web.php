<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    // If user is logged in, redirect to admin dashboard
    if (Auth::check()) {
        return redirect('/admin');
    }

    //If user not yet logged in, redirect to login page
    return redirect('/admin/login');
});

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');

Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.update');

Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'en|ms']], function () {
    Route::get('forgot-password', function ($locale) {
        app()->setLocale($locale);
        return view('auth.forgot-password');
    })->name('password.request');
});