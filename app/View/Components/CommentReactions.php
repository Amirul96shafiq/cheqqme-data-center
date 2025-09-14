<?php

namespace App\View\Components;

use App\Models\Comment;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CommentReactions extends Component
{
    public Comment $comment;

    /**
     * Create a new component instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.comment-reactions');
    }

    /**
     * Get reactions grouped by emoji
     */
    public function getReactions()
    {
        return $this->comment->reactions()
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
                    'user_reacted' => $emojiReactions->contains('user_id', auth()->id()),
                ];
            })
            ->values();
    }
}
