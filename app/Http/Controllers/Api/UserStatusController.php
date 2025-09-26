<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OnlineStatus\PresenceStatusManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserStatusController extends Controller
{
    /**
     * Update user's online status
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $request->validate([
                'status' => 'required|string|in:online,away,dnd,invisible',
            ]);

            $newStatus = $request->input('status');

            // Update status using presence manager
            PresenceStatusManager::handleManualChange($user, $newStatus);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $newStatus,
                    'updated_at' => now()->toISOString(),
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current user's status
     */
    public function getStatus(): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->online_status,
                'avatar' => $user->avatar_url ?? null,
                'last_seen' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Get online users (for debugging/admin purposes)
     */
    public function getOnlineUsers(): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // This would typically be handled by the presence channel
        // But we can provide a fallback for debugging
        $onlineUsers = PresenceStatusManager::getOnlineUsers();

        return response()->json([
            'success' => true,
            'online_users' => $onlineUsers,
            'count' => count($onlineUsers),
        ]);
    }
}
