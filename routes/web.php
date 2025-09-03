<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// OpenAI logs endpoint (web UI)
// OpenAI logs API endpoint moved to routes/api.php ( Sanctum-protected )
Route::get('/openai-logs', [\App\Http\Controllers\OpenaiLogController::class, 'index'])->name('openai.logs')->middleware('auth');

// Forgot password route
Route::get('forgot-password', function () {
    App::setLocale(session('locale', config('app.locale')));

    return view('auth.forgot-password');
})->name('password.request');

// Forgot password route (post)
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// Reset password route
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');

// Reset password route (post)
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Set locale route
Route::post('/set-locale', function (Request $request) {
    $locale = $request->input('locale');
    session(['locale' => $locale]);

    return back();
})->name('locale.set');

// Home route
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin');
    }

    return redirect('/admin/login');
});

// Login route
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
    Route::get('/chatbot/conversations', [ChatbotController::class, 'listConversations'])->name('chatbot.list');
    Route::get('/chatbot/session', [ChatbotController::class, 'getSessionInfo'])->name('chatbot.session');
    Route::get('/chatbot/conversation', [ChatbotController::class, 'getConversationHistory'])->name('chatbot.history');
    Route::post('/chatbot/clear', [ChatbotController::class, 'clearConversation'])->name('chatbot.clear');

    // WebSocket broadcast authentication
    Route::post('/broadcasting/auth', [\App\Http\Controllers\BroadcastController::class, 'authenticate'])
        ->name('broadcasting.auth');

    // Fallback polling endpoint (kept for compatibility, will be removed after WebSocket migration)
    Route::get('/action-board/assigned-active-count', function () {
        if (!auth()->check()) {
            return response()->json(['count' => 0]);
        }
        $count = \App\Models\Task::query()
            ->where('assigned_to', 'like', '%' . auth()->id() . '%')
            ->whereIn('status', ['todo', 'in_progress', 'toreview'])
            ->count();

        return response()->json(['count' => $count]);
    })->name('action-board.assigned-active-count');
});
