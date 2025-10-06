<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Presence channel for online users
Broadcast::channel('online-users', function (User $user) {
    // Return user data that will be available to other users
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(), // allow frontend to render Filament default when null
        'status' => $user->online_status ?? 'online',
        'last_seen' => now()->toISOString(),
    ];
});

// Private channel for user notifications (Filament)
Broadcast::channel('App.Models.User.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for user-specific data
Broadcast::channel('user.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

// Presence channel for users currently viewing a specific task edit page
Broadcast::channel('task-viewers.{taskId}', function (User $user, int $taskId) {
    // Authorize any authenticated user to join; return minimal profile data
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(), // allow frontend to render Filament default when null
    ];
});

// Presence channel for users viewing the Action Board (board-wide)
Broadcast::channel('board-viewers.{boardId}', function (User $user, string $boardId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(),
    ];
});

// Presence channel for users viewing a specific Trello board edit page
Broadcast::channel('trello-board-viewers.{boardId}', function (User $user, int $boardId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(),
    ];
});

// Presence channel for users viewing a specific client edit page
Broadcast::channel('client-viewers.{clientId}', function (User $user, int $clientId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(),
    ];
});

// Presence channel for users viewing a specific project edit page
Broadcast::channel('project-viewers.{projectId}', function (User $user, int $projectId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(),
    ];
});

// Presence channel for users viewing a specific document edit page
Broadcast::channel('document-viewers.{documentId}', function (User $user, int $documentId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(),
    ];
});

// Presence channel for users viewing a specific important URL edit page
Broadcast::channel('important-url-viewers.{urlId}', function (User $user, int $urlId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(),
    ];
});

// Presence channel for users viewing a specific phone number edit page
Broadcast::channel('phone-number-viewers.{phoneId}', function (User $user, int $phoneId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(),
    ];
});

// Presence channel for users viewing a specific user edit page
Broadcast::channel('user-viewers.{userId}', function (User $user, int $userId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->getFilamentAvatarUrl(),
    ];
});
