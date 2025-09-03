<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastController extends Controller
{
    /**
     * Authenticate the broadcast channel
     */
    public function authenticate(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $channelName = $request->input('channel_name');
        $userId = Auth::id();

        // Only allow users to subscribe to their own private channel
        if ($channelName === 'private-user.' . $userId) {
            return response()->json([
                'auth' => 'authorized',
                'channel_data' => [
                    'user_id' => $userId,
                ],
            ]);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}
