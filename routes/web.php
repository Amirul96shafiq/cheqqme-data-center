<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Models\Comment;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

Route::get('forgot-password', function () {
    App::setLocale(session('locale', config('app.locale')));
    return view('auth.forgot-password');
})->name('password.request');

Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('reset-password/{token}', function ($token) {
    App::setLocale(session('locale', config('app.locale')));
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');

Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::post('/set-locale', function (Request $request) {
    $locale = $request->input('locale');
    session(['locale' => $locale]);
    return back();
})->name('locale.set');

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin');
    }
    return redirect('/admin/login');
});

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Comment routes (controller based)
Route::middleware('auth')->group(function () {
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});
