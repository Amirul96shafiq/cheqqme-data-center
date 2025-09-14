<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CommentReactionController extends Controller
{
    use ApiResponseTrait;

    /**
     * Add a reaction to a comment
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'comment_id' => 'required|exists:comments,id',
                'emoji' => 'required|string|max:10',
            ]);

            $comment = Comment::findOrFail($validated['comment_id']);
            $user = Auth::user();

            // Check if user already reacted with this emoji
            $existingReaction = CommentReaction::where([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
            ])->first();

            if ($existingReaction) {
                // Remove existing reaction (toggle off)
                $existingReaction->delete();

                return $this->successResponse([
                    'action' => 'removed',
                    'emoji' => $validated['emoji'],
                    'reaction_count' => $comment->reactions()->where('emoji', $validated['emoji'])->count(),
                ], 'Reaction removed successfully');
            }

            // Create new reaction
            $reaction = CommentReaction::create([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
            ]);

            return $this->successResponse([
                'action' => 'added',
                'emoji' => $validated['emoji'],
                'reaction_count' => $comment->reactions()->where('emoji', $validated['emoji'])->count(),
                'reaction' => [
                    'id' => $reaction->id,
                    'emoji' => $reaction->emoji,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'name' => $user->name,
                    ],
                ],
            ], 'Reaction added successfully');

        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Validation failed',
                422,
                ['errors' => $e->errors()]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to add reaction',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Remove a reaction from a comment
     */
    public function destroy(Request $request, Comment $comment)
    {
        try {
            $validated = $request->validate([
                'emoji' => 'required|string|max:10',
            ]);

            $user = Auth::user();

            $reaction = CommentReaction::where([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
            ])->first();

            if (! $reaction) {
                return $this->errorResponse(
                    'Reaction not found',
                    404
                );
            }

            $reaction->delete();

            return $this->successResponse([
                'action' => 'removed',
                'emoji' => $validated['emoji'],
                'reaction_count' => $comment->reactions()->where('emoji', $validated['emoji'])->count(),
            ], 'Reaction removed successfully');

        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Validation failed',
                422,
                ['errors' => $e->errors()]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to remove reaction',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get reactions for a comment
     */
    public function index(Comment $comment)
    {
        try {
            $reactions = $comment->reactions()
                ->with('user:id,username,name')
                ->get()
                ->groupBy('emoji')
                ->map(function ($emojiReactions) {
                    return [
                        'emoji' => $emojiReactions->first()->emoji,
                        'count' => $emojiReactions->count(),
                        'users' => $emojiReactions->map(function ($reaction) {
                            return [
                                'id' => $reaction->user->id,
                                'username' => $reaction->user->username,
                                'name' => $reaction->user->name,
                            ];
                        })->toArray(),
                        'user_reacted' => $emojiReactions->contains('user_id', Auth::id()),
                    ];
                })
                ->values();

            return $this->successResponse(
                $reactions,
                'Reactions retrieved successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve reactions',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
