<?php

namespace App\View\Components;

use App\Models\User;
use App\Services\OnlineStatus\StatusConfig;
use App\View\Components\Traits\HasOnlineStatus;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InteractiveOnlineStatusIndicator extends Component
{
    use HasOnlineStatus;

    public User $user;

    public string $size;

    public bool $showTooltip;

    public string $position;

    /**
     * Create a new component instance.
     */
    public function __construct(User $user, ?string $size = null, bool $showTooltip = false, ?string $position = null)
    {
        $this->user = $user;
        $this->size = $size ?? StatusConfig::getDefaultSize();
        $this->showTooltip = $showTooltip;
        $this->position = $position ?? 'bottom';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.interactive-online-status-indicator');
    }
}
