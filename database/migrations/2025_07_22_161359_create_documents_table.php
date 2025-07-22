<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Document details
            $table->string('title');
            $table->enum('type', ['external', 'internal']);
            $table->string('url')->nullable(); // for external type documents, user can provide a URL
            $table->string('file_path')->nullable(); // for internal type documents, user can upload a file

            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null'); // Link to the project
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null'); // Link to the client

            $table->text('notes')->nullable(); // Additional notes about the document
            $table->timestamps();
            $table->softDeletes(); // Enable trash/restore functionality
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
