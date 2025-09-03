<?php

namespace App\Livewire;

use Livewire\Component;

class ApiDocumentation extends Component
{
    public $baseUrl;

    public $apiDocsUrl;

    public $apiKey;

    public $maskedApiKey;

    public function mount()
    {
        $this->baseUrl = config('app.url').'/api';
        $this->apiDocsUrl = route('api.documentation', [], false);
        $this->apiKey = auth()->user()?->api_key ?? 'YOUR_API_KEY';
        $this->maskedApiKey = $this->getMaskedApiKey();
    }

    public function getMaskedApiKey()
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_API_KEY') {
            return $this->apiKey;
        }

        $length = strlen($this->apiKey);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        // Show first 4 and last 4 characters, mask the rest
        $first = substr($this->apiKey, 0, 4);
        $last = substr($this->apiKey, -4);
        $masked = str_repeat('*', $length - 8);

        return $first.$masked.$last;
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
