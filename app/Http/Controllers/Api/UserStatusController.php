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

    /**
     * Set user as away due to inactivity
     */
    public function setAwayDueToInactivity(): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            PresenceStatusManager::setAwayDueToInactivity($user);

            return response()->json([
                'success' => true,
                'message' => 'User set to away due to inactivity.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $user->fresh()->online_status,
                    'updated_at' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set away status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set user as invisible due to tab blur
     */
    public function setInvisibleDueToTabBlur(): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            PresenceStatusManager::setInvisibleDueToTabBlur($user);

            return response()->json([
                'success' => true,
                'message' => 'User set to invisible due to tab blur.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $user->fresh()->online_status,
                    'updated_at' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set invisible status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore user from auto-status (away/invisible) to online
     */
    public function restoreFromAutoStatus(): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $previousStatus = $user->online_status;
            PresenceStatusManager::restoreFromAutoStatus($user);

            return response()->json([
                'success' => true,
                'message' => 'User restored from auto-status to online.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $user->fresh()->online_status,
                    'previous_status' => $previousStatus,
                    'updated_at' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore from auto-status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
