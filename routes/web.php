<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\WeatherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// OpenAI logs endpoint (web UI)
// OpenAI logs API endpoint moved to routes/api.php ( Sanctum-protected )
Route::get('/openai-logs', [\App\Http\Controllers\OpenaiLogController::class, 'index'])->name('openai.logs')->middleware('auth');

// Settings backup table AJAX endpoint
Route::get('/settings/backup-table', [\App\Filament\Pages\Settings::class, 'getBackupTable'])->name('settings.backup-table')->middleware('auth');

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
    $redirect = $request->input('redirect');

    session(['locale' => $locale]);

    // If it's an AJAX request, return JSON
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json(['success' => true, 'locale' => $locale]);
    }

    // If a specific redirect URL is provided, use it
    if ($redirect) {
        return redirect($redirect);
    }

    // Fallback to referer-based redirect
    $referer = $request->header('referer');
    if ($referer && str_contains($referer, '/admin/login')) {
        return redirect('/admin/login');
    } elseif ($referer && str_contains($referer, '/login')) {
        return redirect('/login');
    } elseif (str_contains($request->header('referer', ''), 'forgot-password')) {
        return redirect('/forgot-password');
    } elseif (str_contains($request->header('referer', ''), 'reset-password')) {
        return redirect()->back();
    }

    return redirect()->back();
})->middleware('web')->name('locale.set');

// Home route
Route::get('/', function () {
    return redirect('/admin');
});

// Test route for mentions
// Route::get('/test-mentions', function () {
//     $user = \App\Models\User::first();
//     $task = \App\Models\Task::first();

//     if (! $user || ! $task) {
//         return response()->json([
//             'error' => 'No user or task found. Please ensure you have test data.',
//             'users_count' => \App\Models\User::count(),
//             'tasks_count' => \App\Models\Task::count(),
//         ]);
//     }

//     return view('test-mentions', compact('user', 'task'));
// });

// Login route
Route::get('/login', function () {
    App::setLocale(session('locale', config('app.locale')));

    return view('vendor.filament-panels.pages.auth.login');
})->middleware('web')->name('login');

// Admin login route (custom)
Route::get('/admin/login', function () {
    App::setLocale(session('locale', config('app.locale')));

    return view('vendor.filament-panels.pages.auth.login');
})->middleware('web')->name('admin.login');

// Admin login route (custom) - POST
Route::post('/admin/login', function (Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    $attemptCredentials = [
        $loginField => $credentials['email'],
        'password' => $credentials['password'],
    ];

    if (Auth::attempt($attemptCredentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        // Redirect to Filament admin dashboard
        return redirect()->route('filament.admin.pages.dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->name('admin.login');

// Google OAuth routes
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/google/popup-callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'showPopupCallback'])->name('auth.google.popup-callback');

// Microsoft OAuth routes
// Route::get('/auth/microsoft', [\App\Http\Controllers\Auth\MicrosoftAuthController::class, 'redirectToMicrosoft'])->name('auth.microsoft');
// Route::get('/auth/microsoft/callback', [\App\Http\Controllers\Auth\MicrosoftAuthController::class, 'handleMicrosoftCallback'])->name('auth.microsoft.callback');
// Route::get('/auth/microsoft/popup-callback', [\App\Http\Controllers\Auth\MicrosoftAuthController::class, 'showPopupCallback'])->name('auth.microsoft.popup-callback');

// Debug route for Microsoft OAuth testing
// Route::get('/debug/microsoft', function () {
//     try {
//         // Use custom Microsoft provider that uses 'consumers' tenant
//         $driver = new \App\Http\Controllers\Auth\CustomMicrosoftProvider(
//             request(),
//             config('services.microsoft.client_id'),
//             config('services.microsoft.client_secret'),
//             config('services.microsoft.redirect')
//         );

//         $driver->setHttpClient(new \GuzzleHttp\Client([
//             'verify' => false,
//         ]));

//         $url = $driver->redirect()->getTargetUrl();

//         return response()->json([
//             'url' => $url,
//             'config' => config('services.microsoft'),
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString(),
//             'config' => config('services.microsoft'),
//         ], 500);
//     }
// });

// Comment routes (controller based)
Route::middleware('auth')->group(function () {
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Emoji reaction routes
    Route::post('/comments/{comment}/emoji', [CommentController::class, 'addEmojiReaction'])->name('comments.emoji.add');
    Route::delete('/comments/{comment}/emoji', [CommentController::class, 'removeEmojiReaction'])->name('comments.emoji.remove');
    Route::get('/comments/{comment}/emoji', [CommentController::class, 'getEmojiReactions'])->name('comments.emoji.get');
    Route::post('/comments/emoji/batch', [CommentController::class, 'getEmojiReactionsForComments'])->name('comments.emoji.batch');

    // Comment reactions (web interface)
    Route::get('/api/comments/{comment}/reactions', [\App\Http\Controllers\CommentReactionController::class, 'index'])->name('web.comment-reactions.index');
    Route::post('/api/comment-reactions', [\App\Http\Controllers\CommentReactionController::class, 'store'])->name('web.comment-reactions.store');
    Route::delete('/api/comments/{comment}/reactions', [\App\Http\Controllers\CommentReactionController::class, 'destroy'])->name('web.comment-reactions.destroy');

    // Chatbot routes
    Route::post('/chatbot/chat', [ChatbotController::class, 'chat'])->name('chatbot.chat');
    Route::get('/chatbot/conversations', [ChatbotController::class, 'listConversations'])->name('chatbot.list');
    Route::get('/chatbot/session', [ChatbotController::class, 'getSessionInfo'])->name('chatbot.session');
    Route::get('/chatbot/conversation', [ChatbotController::class, 'getConversationHistory'])->name('chatbot.history');
    Route::post('/chatbot/clear', [ChatbotController::class, 'clearConversation'])->name('chatbot.clear');

    // Chatbot backup routes
    Route::get('/chatbot/backup/{backup}/download', [ChatbotController::class, 'downloadBackup'])->name('chatbot.backup.download');
    Route::post('/chatbot/backup/create', [ChatbotController::class, 'createManualBackup'])->name('chatbot.backup.create');

    // WebSocket broadcast authentication
    Route::post('/broadcasting/auth', [\App\Http\Controllers\BroadcastController::class, 'authenticate'])
        ->name('broadcasting.auth');

    // Fallback polling endpoint (kept for compatibility, will be removed after WebSocket migration)
    Route::get('/action-board/assigned-active-count', function () {
        if (! auth()->check()) {
            return response()->json(['count' => 0]);
        }
        $count = \App\Models\Task::query()
            ->where('assigned_to', 'like', '%'.auth()->id().'%')
            ->whereIn('status', ['todo', 'in_progress', 'toreview'])
            ->count();

        return response()->json(['count' => $count]);
    })->name('action-board.assigned-active-count');

    // Online status update route
    Route::post('/admin/profile/update-online-status', function (Request $request) {
        $request->validate([
            'online_status' => \App\Services\OnlineStatus\StatusManager::getValidationRules(),
        ]);

        $user = auth()->user();
        \App\Services\OnlineStatus\StatusController::handleManualChange($user, $request->online_status);

        return response()->json([
            'success' => true,
            'message' => __('user.indicator.online_status_updated'),
            'status' => $user->fresh()->online_status,
        ]);
    })->name('profile.update-online-status');

    // User activity tracking route
    Route::post('/admin/profile/track-activity', function (Request $request) {
        $user = auth()->user();
        \App\Services\OnlineStatus\StatusController::checkAndUpdateStatus($user);

        return response()->json(['success' => true]);
    })->name('profile.track-activity');
});

// Weather API routes
Route::middleware('auth')->group(function () {
    Route::get('/weather/current', [WeatherController::class, 'getCurrentWeather'])->name('weather.current');
    Route::get('/weather/forecast', [WeatherController::class, 'getForecast'])->name('weather.forecast');
    Route::get('/weather/data', [WeatherController::class, 'getWeatherData'])->name('weather.data');
    Route::get('/weather/user-location', [WeatherController::class, 'checkUserLocation'])->name('weather.user-location');
    Route::post('/weather/location', [WeatherController::class, 'updateLocation'])->name('weather.location');
    Route::post('/weather/clear-cache', [WeatherController::class, 'clearCache'])->name('weather.clear-cache');
});

// Microsoft "coming soon" notification route
Route::get('/microsoft/coming-soon', function (Illuminate\Http\Request $request) {
    $message = $request->get('message', 'Microsoft Sign-in: This feature is coming soon!');

    session(['microsoft_coming_soon_message' => $message]);

    return redirect()->route('admin.login');
})->name('microsoft.coming-soon');
