<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Livewire\ChatbotWidget as ChatbotLivewire;

class ChatbotWidget extends Widget
{
    protected static string $view = 'filament.widgets.chatbot-widget';
    
    protected static ?int $sort = 100;
    
    protected int | string | array $columnSpan = 'full';
    
    public static function canView(): bool
    {
        return auth()->check();
    }
    
    protected function getViewData(): array
    {
        return [
            'chatbotComponent' => new ChatbotLivewire(),
        ];
    }
}