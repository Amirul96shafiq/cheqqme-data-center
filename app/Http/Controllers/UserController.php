<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserApiResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = User::query()
                ->with(['updatedBy']);

            // Add exact ID search (highest priority)
            if ($request->filled('id')) {
                $id = $request->input('id');
                $query->where('id', $id);
            }
            // Add search functionality (if no specific ID is provided)
            elseif ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    // Check if search is numeric (could be an ID)
                    if (is_numeric($search)) {
                        $q->where('id', $search)
                          ->orWhere('name', 'like', "%{$search}%")
                          ->orWhere('username', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    } else {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('username', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    }
                });
            }

            // Add filtering by username
            if ($request->filled('username')) {
                $username = $request->input('username');
                $query->where('username', 'like', "%{$username}%");
            }

            // Add filtering by email
            if ($request->filled('email')) {
                $email = $request->input('email');
                $query->where('email', 'like', "%{$email}%");
            }

            // Add filtering by updated_by
            if ($request->filled('updated_by')) {
                $updatedBy = $request->input('updated_by');
                $query->where('updated_by', $updatedBy);
            }

            // Add filtering by has_api_key (users with API keys)
            if ($request->filled('has_api_key')) {
                $hasApiKey = $request->boolean('has_api_key');
                if ($hasApiKey) {
                    $query->whereNotNull('api_key');
                } else {
                    $query->whereNull('api_key');
                }
            }

            // Add filtering by email_verified (users with verified emails)
            if ($request->filled('email_verified')) {
                $emailVerified = $request->boolean('email_verified');
                if ($emailVerified) {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            }

            // Add filtering by has_avatar (users with avatars)
            if ($request->filled('has_avatar')) {
                $hasAvatar = $request->boolean('has_avatar');
                if ($hasAvatar) {
                    $query->whereNotNull('avatar');
                } else {
                    $query->whereNull('avatar');
                }
            }

            // Add filtering by has_cover_image (users with cover images)
            if ($request->filled('has_cover_image')) {
                $hasCoverImage = $request->boolean('has_cover_image');
                if ($hasCoverImage) {
                    $query->whereNotNull('cover_image');
                } else {
                    $query->whereNull('cover_image');
                }
            }

            // Add filtering by timezone
            if ($request->filled('timezone')) {
                $timezone = $request->input('timezone');
                $query->where('timezone', $timezone);
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $limit = (int) $request->input('limit', 50);
            $users = $query->limit($limit)->get();

            return $this->successResponse(
                UserApiResource::collection($users),
                'Users retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve users',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
