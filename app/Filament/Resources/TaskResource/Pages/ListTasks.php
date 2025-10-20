<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\Page;

class ListTasks extends Page
{
    protected static string $resource = TaskResource::class;

    protected static string $view = 'filament.resources.task-resource.pages.list-tasks';

    public function mount(): void
    {
        // Redirect to action board page using Livewire redirect
        $this->redirect('/admin/action-board');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
