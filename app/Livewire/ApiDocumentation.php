<?php

namespace App\Livewire;

use Livewire\Component;

class ApiDocumentation extends Component
{
    public $baseUrl;

    public $apiDocsUrl;

    public $apiKey;

    public function mount()
    {
        $this->baseUrl = config('app.url') . '/api';
        $this->apiDocsUrl = route('api.documentation', [], false);
        $this->apiKey = auth()->user()?->api_key ?? 'YOUR_API_KEY';
    }

    public function copyToClipboard($text)
    {
        $this->dispatch('copy-to-clipboard', text: $text);
    }

    public function render()
    {
        return view('livewire.api-documentation');
    }
}
