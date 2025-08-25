<?php

namespace App\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class SelectedItemsLinks extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $selectedProjects = [];

    public ?array $selectedDocuments = [];

    public ?array $selectedUrls = [];

    public ?int $clientId = null;

    public function mount($selectedProjects = [], $selectedDocuments = [], $selectedUrls = [], $clientId = null)
    {
        $this->selectedProjects = $selectedProjects;
        $this->selectedDocuments = $selectedDocuments;
        $this->selectedUrls = $selectedUrls;
        $this->clientId = $clientId;
    }

    public function render()
    {
        return view('livewire.selected-items-links');
    }
}
