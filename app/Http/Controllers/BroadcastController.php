<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastController extends \Illuminate\Broadcasting\BroadcastController
{
    /**
     * Authenticate the broadcast channel
     */
    public function authenticate(Request $request)
    {
        $channelName = $request->input('channel_name');

        \Log::info('Broadcasting auth attempt', [
            'channel_name' => $channelName,
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);

        if (! Auth::check()) {
            \Log::warning('Broadcasting auth failed - user not authenticated', [
                'channel_name' => $channelName,
            ]);

            return response(json_encode(['error' => 'Unauthorized']), 403, [
                'Content-Type' => 'application/json',
            ]);
        }

        $userId = Auth::id();
        $user = Auth::user();

        // Handle presence channels (both 'presence-' prefix and 'online-users' channel)
        if (str_starts_with($channelName, 'presence-') || $channelName === 'online-users') {
            $response = [
                'auth' => 'authorized',
                'channel_data' => [
                    'user_id' => $user->id,
                    'user_info' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar_url ?? null,
                        'status' => $user->online_status ?? 'online',
                        'last_seen' => now()->toISOString(),
                    ],
                ],
            ];

            \Log::info('Broadcasting auth successful for presence channel', [
                'channel_name' => $channelName,
                'user_id' => $user->id,
                'response' => $response,
            ]);

            // Return the response directly without wrapping in JSON
            return response(json_encode($response), 200, [
                'Content-Type' => 'application/json',
            ]);
        }

        // Handle private channels - only allow users to subscribe to their own private channel
        if ($channelName === 'private-user.'.$userId || $channelName === 'private-App.Models.User.'.$userId) {
            $response = [
                'auth' => 'authorized',
                'channel_data' => [
                    'user_id' => $userId,
                ],
            ];

            \Log::info('Broadcasting auth successful for private channel', [
                'channel_name' => $channelName,
                'user_id' => $userId,
                'response' => $response,
            ]);

            // Return the response directly without wrapping in JSON
            return response(json_encode($response), 200, [
                'Content-Type' => 'application/json',
            ]);
        }

        \Log::warning('Broadcasting auth failed - channel not authorized', [
            'channel_name' => $channelName,
            'user_id' => $userId,
        ]);

        return response(json_encode(['error' => 'Forbidden']), 403, [
            'Content-Type' => 'application/json',
        ]);
    }
}
