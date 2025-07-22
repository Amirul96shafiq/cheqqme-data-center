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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // Project details
            $table->string('title');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->string('status')->default('Choose Project Status')->nullable(); // Default status // e.g. Planning, In Progress, Completed
            
            $table->timestamps();
            $table->softDeletes(); // Enable trash/restore functionality
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
