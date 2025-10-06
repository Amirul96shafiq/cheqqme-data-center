<?php

namespace App\Http\Controllers;

use App\Services\UserMentionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserMentionController extends Controller
{
    public function __construct(
        private UserMentionService $userMentionService
    ) {}

    /**
     * Get users for mention search
     */
    public function search(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'search' => 'sometimes|string|max:100',
                'limit' => 'sometimes|integer|min:1|max:500',
            ]);

            $users = $this->userMentionService->getUsersForMentionSearch();

            return response()->json([
                'success' => true,
                'users' => $users,
                'count' => $users->count(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in UserMentionController::search', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users for mention search',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
