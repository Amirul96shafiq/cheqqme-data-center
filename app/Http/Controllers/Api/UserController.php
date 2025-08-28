<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile information
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Get the authenticated user's tasks
     */
    // public function tasks(Request $request): JsonResponse
    // {
    //     $user = $request->user();
    //     $tasks = $user->tasks()
    //         ->with(['project', 'client'])
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(20);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $tasks
    //     ]);
    // }

    /**
     * Get the authenticated user's projects
     */
    // public function projects(Request $request): JsonResponse
    // {
    //     $user = $request->user();
    //     $projects = $user->projects()
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(20);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $projects
    //     ]);
    // }

    /**
     * Get API key information for the authenticated user
     */
    public function apiKeyInfo(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'has_api_key' => $user->hasApiKey(),
                'api_key_generated_at' => $user->api_key_generated_at,
                'masked_api_key' => $user->getMaskedApiKey(),
            ],
        ]);
    }
}
