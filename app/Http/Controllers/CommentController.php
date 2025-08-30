<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentApiResource;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Retrieve a single comment by ID (supports soft-deleted via withTrashed)
    public function show($comment)
    {
        $commentModel = Comment::withTrashed()->findOrFail($comment);

        return response()->json(['comment' => new CommentApiResource($commentModel)]);
    }

    public function index(Request $request)
    {
        $query = Comment::query()
            ->with(['task', 'user']);

        $limit = (int) $request->input('limit', 50);
        $comments = $query->limit($limit)->get();

        return response()->json(['comments' => CommentApiResource::collection($comments)]);
    }

    /**
     * Store a new comment
     */
    public function store(Request $request)
    {
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

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'comment_id' => $comment->id]);
        }

        return back()->with('success', 'Comment created');
    }

    /**
     * Update a comment
     */
    public function update(Comment $comment, Request $request)
    {
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

        return response()->json(['success' => true]);
    }

    /**
     * Delete a comment
     */
    public function destroy(Comment $comment, Request $request)
    {
        // Validate the request
        abort_unless($comment->user_id === $request->user()->id, 403);
        $comment->delete();

        return response()->json(['success' => true]);
    }
}
