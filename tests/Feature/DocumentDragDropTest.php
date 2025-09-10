<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentDragDropTest extends TestCase
{
    use RefreshDatabase;

    public function test_drag_drop_script_is_included()
    {
        // Test that the drag-drop-upload.js file exists in the built assets
        $assetFiles = glob(public_path('build/assets/drag-drop-upload-*.js'));
        $this->assertNotEmpty($assetFiles, 'Drag-drop-upload script should be built in assets');
    }

    public function test_document_model_has_required_fields()
    {
        $document = new \App\Models\Document;
        $fillable = $document->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('file_path', $fillable);
    }
}
