<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Document Upload Handler Component
 *
 * Handles the auto-fill functionality for the document creation form
 * when files are dragged and dropped from other pages.
 */
class DocumentUploadHandler extends Component
{
    public function render()
    {
        return view('livewire.document-upload-handler');
    }
}
