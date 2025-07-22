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
        Schema::create('important_urls', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('url');

            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null'); // Link to the project
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null'); // Link to the client

            $table->text('notes')->nullable(); // Additional notes about the Important URL
            $table->timestamps();
            $table->softDeletes(); // Enable trash/restore functionality
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('important_urls');
    }
};
