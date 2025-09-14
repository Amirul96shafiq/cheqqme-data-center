<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EmojiPicker extends Component
{
    public string $commentId;

    public string $triggerClass;

    /**
     * Create a new component instance.
     */
    public function __construct(string $commentId, string $triggerClass = 'emoji-picker-trigger')
    {
        $this->commentId = $commentId;
        $this->triggerClass = $triggerClass;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.emoji-picker');
    }

    /**
     * Get popular emojis for reactions
     */
    public function getPopularEmojis(): array
    {
        return [
            '👍',
            '👎',
            '❤️',
            '😂',
            '😮',
            '😢',
            '😡',
            '🎉',
            '👏',
            '🔥',
            '💯',
            '✨',
            '🚀',
            '💡',
            '🎯',
            '⭐',
            '💪',
            '🤔',
            '👀',
            '🙌',
            '💖',
            '😊',
            '🤝',
            '🎊',
        ];
    }
}
