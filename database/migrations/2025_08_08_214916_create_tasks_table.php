<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Task details
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo');
            $table->integer('order_column')->nullable();
            $table->text('notes')->nullable(); // Additional notes about the document
            $table->json('extra_information')->nullable(); // Extra info about the task
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // Enable trash/restore functionality
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
