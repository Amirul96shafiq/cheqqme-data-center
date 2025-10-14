<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleCalendarController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\WeatherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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

// // Online status test page
// Route::get('/online-status-test', function () {
//     return view('online-status-test');
// })->name('online-status-test')->middleware('auth');

// // Temporary login route for testing (remove in production)
// Route::get('/test-login', function () {
//     // Auto-login the first user for testing
//     $user = \App\Models\User::first();
//     if ($user) {
//         auth()->login($user);

//         return redirect('/online-status-test');
//     }

//     return 'No users found. Please create a user first.';
// })->name('test-login');

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
    // Set locale from session
    App::setLocale(session('locale', config('app.locale')));

    $credentials = $request->validate([
        'email' => ['required', 'string'],
        'password' => ['required'],
    ]);

    // Determine login field: check if it's a phone number, email, or username
    $input = $credentials['email'];
    $loginField = 'username'; // default

    // Check if input is a phone number (contains only digits, potentially with + at start)
    if (preg_match('/^\+?\d+$/', $input)) {
        // Remove + if present and use phone field
        $loginField = 'phone';
        $input = preg_replace('/\D+/', '', $input); // Keep only digits
    } elseif (filter_var($input, FILTER_VALIDATE_EMAIL)) {
        $loginField = 'email';
    }

    // Rate limiting: track by input + IP address for security
    $rateLimitKey = 'login-attempt:'.strtolower($input).':'.$request->ip();
    $maxAttempts = 5;
    $decaySeconds = 60; // 1 minute lockout

    // Check if too many attempts have been made
    if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
        $seconds = RateLimiter::availableIn($rateLimitKey);
        $minutes = ceil($seconds / 60);

        return back()->withErrors([
            'email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => $minutes]),
        ])->onlyInput('email');
    }

    $attemptCredentials = [
        $loginField => $input,
        'password' => $credentials['password'],
    ];

    if (Auth::attempt($attemptCredentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        // Clear rate limit on successful login
        RateLimiter::clear($rateLimitKey);

        // Redirect to Filament admin dashboard
        return redirect()->route('filament.admin.pages.dashboard');
    }

    // Increment failed login attempts
    RateLimiter::hit($rateLimitKey, $decaySeconds);

    // Calculate remaining attempts
    $attemptsLeft = $maxAttempts - RateLimiter::attempts($rateLimitKey);

    if ($attemptsLeft > 0) {
        return back()->withErrors([
            'email' => trans('auth.failed_with_attempts', ['attempts' => $attemptsLeft]),
        ])->onlyInput('email');
    }

    // This is the last failed attempt
    return back()->withErrors([
        'email' => trans('auth.locked_out'),
    ])->onlyInput('email');
})->name('admin.login');

// Google OAuth routes
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/google/popup-callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'showPopupCallback'])->name('auth.google.popup-callback');

// Google Calendar OAuth routes
Route::get('/auth/google/calendar', [GoogleCalendarController::class, 'redirectToGoogleCalendar'])->name('auth.google.calendar')->middleware('auth');
Route::get('/auth/google/calendar/callback', [GoogleCalendarController::class, 'handleGoogleCalendarCallback'])->name('auth.google.calendar.callback');
Route::post('/auth/google/calendar/disconnect', [GoogleCalendarController::class, 'disconnectGoogleCalendar'])->name('auth.google.calendar.disconnect')->middleware('auth');
Route::get('/auth/google/calendar/status', [GoogleCalendarController::class, 'checkConnectionStatus'])->name('auth.google.calendar.status')->middleware('auth');

// Debug route for testing OAuth flow (remove in production)
Route::get('/debug/google-calendar', function () {
    $user = auth()->user();

    return response()->json([
        'user_id' => $user?->id,
        'has_token' => ! is_null($user?->google_calendar_token),
        'connected_at' => $user?->google_calendar_connected_at,
        'session_state' => session('google_calendar_state'),
        'auth_url' => app(\App\Services\GoogleMeetService::class)->getAuthUrl('debug'),
    ]);
})->middleware('auth');

// Spotify OAuth routes
Route::get('/auth/spotify', [\App\Http\Controllers\Auth\SpotifyAuthController::class, 'redirectToSpotify'])->name('auth.spotify');
Route::get('/auth/spotify/callback', [\App\Http\Controllers\Auth\SpotifyAuthController::class, 'handleSpotifyCallback'])->name('auth.spotify.callback');
Route::get('/auth/spotify/popup-callback', [\App\Http\Controllers\Auth\SpotifyAuthController::class, 'showPopupCallback'])->name('auth.spotify.popup-callback');
Route::post('/auth/spotify/clear-session', [\App\Http\Controllers\Auth\SpotifyAuthController::class, 'clearSession'])->name('auth.spotify.clear-session');

// Spotify Web Playback SDK routes
Route::middleware('auth')->group(function () {
    Route::get('/api/spotify/token', [\App\Http\Controllers\SpotifyPlayerController::class, 'getToken'])->name('spotify.token');
    Route::post('/api/spotify/transfer-playback', [\App\Http\Controllers\SpotifyPlayerController::class, 'transferPlayback'])->name('spotify.transfer-playback');
});

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

    // User status API endpoints (moved from API routes for web interface)
    Route::middleware(['auth'])->group(function () {
        Route::get('/api/user/status', [\App\Http\Controllers\Api\UserStatusController::class, 'getStatus'])->name('api.user.status');
        Route::post('/api/user/status', [\App\Http\Controllers\Api\UserStatusController::class, 'updateStatus'])->name('api.user.status.update');
        Route::post('/api/user/activity', [\App\Http\Controllers\Api\UserStatusController::class, 'updateActivity'])->name('api.user.activity');
        Route::get('/api/user/statuses', [\App\Http\Controllers\Api\UserStatusController::class, 'getAllStatuses'])->name('api.user.statuses');
        Route::post('/api/user/auto-away', [\App\Http\Controllers\Api\UserStatusController::class, 'setAwayDueToInactivity'])->name('api.user.auto-away');
        Route::post('/api/user/auto-invisible', [\App\Http\Controllers\Api\UserStatusController::class, 'setInvisibleDueToTabBlur'])->name('api.user.auto-invisible');
        Route::post('/api/user/restore-auto-status', [\App\Http\Controllers\Api\UserStatusController::class, 'restoreFromAutoStatus'])->name('api.user.restore-auto-status');
    });

    // Web-authenticated mention search for Alpine dropdown (avoids 401 from API route)
    Route::get('/api/users/mention-search', [\App\Http\Controllers\UserMentionController::class, 'search'])->name('web.users.mention-search');

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

    // Online status update route (now uses presence channels)
    Route::post('/admin/profile/update-online-status', function (Request $request) {
        $request->validate([
            'online_status' => \App\Services\OnlineStatus\StatusManager::getValidationRules(),
        ]);

        $user = auth()->user();
        \App\Services\OnlineStatus\PresenceStatusManager::handleManualChange($user, $request->online_status);

        return response()->json([
            'success' => true,
            'message' => __('user.indicator.online_status_updated'),
            'status' => $user->fresh()->online_status,
        ]);
    })->name('profile.update-online-status');

    // User activity tracking route (now uses presence channels)
    Route::post('/admin/profile/track-activity', function (Request $request) {
        $user = auth()->user();

        // With presence channels, we don't need complex activity tracking
        // The presence channel automatically handles join/leave events
        // We only need to ensure the user is marked as online when they're active
        if ($user->online_status !== 'invisible') {
            // Only set to online if user is currently away (auto-away)
            // Don't override manual status changes (dnd, away, etc.)
            if ($user->online_status === 'away' && ! $user->last_status_change) {
                // This is an auto-away user, set them back to online
                \App\Services\OnlineStatus\PresenceStatusManager::setOnline($user);
            }
        }

        return response()->json([
            'success' => true,
            'status' => $user->fresh()->online_status,
        ]);
    })->name('profile.track-activity');

    // Auto-away functionality is now handled by presence channels
    // No manual polling needed

    // Set user to invisible when browser tab is closed
    Route::post('/admin/profile/set-invisible-on-close', function (Request $request) {
        $user = auth()->user();

        // Only set to invisible if user hasn't manually set their status
        // If they have a manual status (last_status_change is not null), preserve it
        if ($user->last_status_change !== null) {
            // User has manually set their status, don't change it
            \Illuminate\Support\Facades\Log::info("User {$user->id} has manual status ({$user->online_status}), not setting to invisible on tab close");

            return response()->json([
                'success' => true,
                'message' => 'User status unchanged (manual status preserved)',
                'status' => $user->online_status,
                'manual_status' => true,
            ]);
        }

        // User has auto status, set to invisible
        $previousStatus = $user->online_status;
        $user->update([
            'online_status' => 'invisible',
            'last_status_change' => null, // Keep null to mark as auto-invisible
        ]);

        \Illuminate\Support\Facades\Log::info("User {$user->id} set to invisible status on browser tab close (auto-invisible) from status: {$previousStatus}");

        return response()->json([
            'success' => true,
            'message' => 'User status set to invisible',
            'previous_status' => $previousStatus,
        ]);
    })->name('profile.set-invisible-on-close');

    // Set user back to online when returning to tab
    Route::post('/admin/profile/set-online-on-return', function (Request $request) {
        $user = auth()->user();

        // If user has a manual status, don't change it
        if ($user->last_status_change !== null) {
            \Illuminate\Support\Facades\Log::info("User {$user->id} has manual status ({$user->online_status}), not changing on tab return");

            return response()->json([
                'success' => true,
                'message' => 'User status unchanged (manual status preserved)',
                'status' => $user->online_status,
                'manual_status' => true,
            ]);
        }

        // Only set to online if user is currently invisible AND it was set automatically (not manually)
        if ($user->online_status === 'invisible') {
            // This is auto-invisible (last_status_change is null), restore to online
            \App\Services\OnlineStatus\PresenceStatusManager::setOnline($user);
            \Illuminate\Support\Facades\Log::info("User {$user->id} set back to online status on tab return (was auto-invisible)");

            return response()->json([
                'success' => true,
                'message' => 'User set back to online (was auto-invisible)',
                'status' => $user->fresh()->online_status,
                'wasAutoInvisible' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User status checked',
            'status' => $user->online_status,
            'wasAutoInvisible' => false,
            'reason' => 'not_invisible',
        ]);
    })->name('profile.set-online-on-return');

    // Debug endpoint to view tab close logs
    // Route::get('/admin/profile/debug-tab-logs', function () {
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Check browser console for localStorage.getItem("tabCloseLogs") to view persistent logs',
    //         'instructions' => [
    //             '1. Open browser console (F12)',
    //             '2. Run: localStorage.getItem("tabCloseLogs")',
    //             '3. Copy the JSON and format it for readability',
    //             '4. Look for beforeunload events and their data',
    //         ],
    //     ]);
    // })->name('profile.debug-tab-logs');

    // Focused comment deep link: redirect to Task edit while preserving focus comment via query param
    Route::get('/admin/tasks/{task}/edit/comments/{comment}', function (\App\Models\Task $task, \App\Models\Comment $comment) {
        // Redirect to Filament Task edit page and carry the focused comment id
        $url = \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task->getKey()]);
        $separator = str_contains($url, '?') ? '&' : '?';

        return redirect()->to($url.$separator.'focus_comment='.$comment->getKey());
    })->whereNumber('task')->whereNumber('comment')->name('admin.tasks.edit.focus');
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

// What's New / Changelog / Commits History routes
Route::get('/changelog', function () {
    try {
        $changelog = \App\Helpers\ChangelogHelper::getPaginatedChangelog(10, request('page', 1));

        // Format commits for JSON response
        $commits = $changelog->map(function ($commit) {
            return [
                'short_hash' => $commit['short_hash'],
                'full_hash' => $commit['full_hash'],
                'date' => $commit['date']->toISOString(),
                'date_formatted' => $commit['date']->format('M j, Y g:i A'),
                'date_relative' => $commit['date']->diffForHumans(),
                'author_name' => $commit['author_name'],
                'author_email' => $commit['author_email'],
                'author_avatar' => $commit['author_avatar'],
                'message' => $commit['message'],
            ];
        });

        return response()->json([
            'success' => true,
            'commits' => $commits,
            'total' => $changelog->total(),
            'pagination' => [
                'current_page' => $changelog->currentPage(),
                'last_page' => $changelog->lastPage(),
                'per_page' => $changelog->perPage(),
                'total' => $changelog->total(),
                'from' => $changelog->firstItem(),
                'to' => $changelog->lastItem(),
                'has_more_pages' => $changelog->hasMorePages(),
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'commits' => [],
            'total' => 0,
            'pagination' => null,
        ], 500);
    }
})->name('changelog');

// Microsoft "coming soon" notification route
Route::get('/microsoft/coming-soon', function (Illuminate\Http\Request $request) {
    $message = $request->get('message', 'Microsoft Sign-in: This feature is coming soon!');

    session(['microsoft_coming_soon_message' => $message]);

    return redirect()->route('admin.login');
})->name('microsoft.coming-soon');
