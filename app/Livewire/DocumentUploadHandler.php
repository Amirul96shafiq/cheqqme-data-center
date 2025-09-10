<?php

namespace App\Livewire;

use Livewire\Component;

class DocumentUploadHandler extends Component
{
  public function mount()
  {
    // This component will handle the file upload process
    // The actual file handling will be done in the DocumentResource
  }

  public function render()
  {
    return view('livewire.document-upload-handler');
  }
}
