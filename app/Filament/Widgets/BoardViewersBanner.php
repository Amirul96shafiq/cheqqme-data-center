<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class BoardViewersBanner extends Widget
{
    protected static string $view = 'components.board-viewers-banner';

    protected function getViewData(): array
    {
        return [
            'boardId' => 'action-board',
        ];
    }
}


