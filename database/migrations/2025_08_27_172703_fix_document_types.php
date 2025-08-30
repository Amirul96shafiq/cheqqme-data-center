<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing documents with invalid type values
        // Convert file extensions to proper document types
        $documents = \App\Models\Document::all();

        foreach ($documents as $document) {
            $currentType = $document->type;

            // If type is a file extension, convert it to 'internal'
            if (in_array($currentType, ['pdf', 'docx', 'xlsx', 'pptx', 'txt', 'jpg', 'png', 'jpeg'])) {
                $document->type = 'internal';
                $document->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
