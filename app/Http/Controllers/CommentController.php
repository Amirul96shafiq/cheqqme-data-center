<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentApiResource;
use App\Models\Comment;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponseTrait;

    // Retrieve a single comment by ID (supports soft-deleted via withTrashed)
    public function show($comment)
    {
        try {
            $commentModel = Comment::withTrashed()->findOrFail($comment);

            return $this->successResponse(
                new CommentApiResource($commentModel),
                'Comment retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve comment',
                404,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Comment::query()
                ->with(['task', 'user']);

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
                          ->orWhere('comment', 'like', "%{$search}%");
                    } else {
                        $q->where('comment', 'like', "%{$search}%");
                    }
                });
            }

            // Add filtering by task_id
            if ($request->filled('task_id')) {
                $taskId = $request->input('task_id');
                $query->where('task_id', $taskId);
            }

            // Add filtering by user_id
            if ($request->filled('user_id')) {
                $userId = $request->input('user_id');
                $query->where('user_id', $userId);
            }

            // Add filtering by has_mentions (comments with mentions)
            if ($request->filled('has_mentions')) {
                $hasMentions = $request->boolean('has_mentions');
                if ($hasMentions) {
                    $query->whereNotNull('mentions')->where('mentions', '!=', '[]');
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('mentions')->orWhere('mentions', '[]');
                    });
                }
            }

            // Add filtering by mention_user_id (comments that mention a specific user)
            if ($request->filled('mention_user_id')) {
                $mentionUserId = $request->input('mention_user_id');
                $query->whereJsonContains('mentions', $mentionUserId);
            }

            // Add filtering by comment_length (short, medium, long)
            if ($request->filled('comment_length')) {
                $commentLength = $request->input('comment_length');
                switch ($commentLength) {
                    case 'short':
                        $query->whereRaw('LENGTH(comment) <= 100');
                        break;
                    case 'medium':
                        $query->whereRaw('LENGTH(comment) > 100 AND LENGTH(comment) <= 500');
                        break;
                    case 'long':
                        $query->whereRaw('LENGTH(comment) > 500');
                        break;
                }
            }

            // Add filtering by date range
            if ($request->filled('created_after')) {
                $createdAfter = $request->input('created_after');
                $query->where('created_at', '>=', $createdAfter);
            }

            if ($request->filled('created_before')) {
                $createdBefore = $request->input('created_before');
                $query->where('created_at', '<=', $createdBefore);
            }

            // Add filtering by updated_by (if available)
            if ($request->filled('updated_by')) {
                $updatedBy = $request->input('updated_by');
                $query->where('updated_by', $updatedBy);
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $limit = (int) $request->input('limit', 50);
            $comments = $query->limit($limit)->get();

            return $this->successResponse(
                CommentApiResource::collection($comments),
                'Comments retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve comments',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Store a new comment
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'comment' => 'required|string|max:1000',
            ]);

            // Extract mentions from comment text
            $mentions = Comment::extractMentions($validated['comment']);

            $comment = Comment::create([
                'task_id' => $validated['task_id'],
                'user_id' => $request->user()->id,
                'comment' => $validated['comment'],
                'mentions' => $mentions,
            ]);

            // Process mentions and send notifications
            $comment->processMentions();

            return $this->successResponse(
                new CommentApiResource($comment),
                'Comment created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create comment',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update a comment
     */
    public function update(Comment $comment, Request $request)
    {
        try {
            // Validate the request
            abort_unless($comment->user_id === $request->user()->id, 403);
            $validated = $request->validate([
                'comment' => 'required|string|max:1000',
            ]);

            // Extract mentions from updated comment text
            $mentions = Comment::extractMentions($validated['comment']);

            $comment->update([
                'comment' => $validated['comment'],
                'mentions' => $mentions,
            ]);

            // Process mentions and send notifications for new mentions
            $comment->processMentions();

            return $this->successResponse(
                new CommentApiResource($comment),
                'Comment updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update comment',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete a comment
     */
    public function destroy(Comment $comment, Request $request)
    {
        try {
            // Validate the request
            abort_unless($comment->user_id === $request->user()->id, 403);
            $comment->delete();

            return $this->successResponse(
                null,
                'Comment deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete comment',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
