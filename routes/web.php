<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ChatbotController;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

    // Chatbot routes
    Route::post('/chatbot/chat', [ChatbotController::class, 'chat'])->name('chatbot.chat');
    Route::get('/chatbot/conversation', [ChatbotController::class, 'getConversationHistory'])->name('chatbot.conversation');
    Route::delete('/chatbot/conversation', [ChatbotController::class, 'clearConversation'])->name('chatbot.clear');

    // Notification routes
    Route::post('/notifications/{id}/mark-as-read', function ($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    })->name('notifications.mark-as-read');

    // Live badge polling endpoint for Action Board navigation badge
    Route::get('/action-board/assigned-active-count', function () {
        if (!auth()->check()) {
            return response()->json(['count' => 0]);
        }
        $count = \App\Models\Task::query()
            ->where('assigned_to', auth()->id())
            ->whereNotIn('status', ['completed', 'archived'])
            ->count();

        return response()->json(['count' => $count]);
    })->name('action-board.assigned-active-count');
});
