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
        Schema::create('chatbot_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('backup_name');
            $table->text('backup_data'); // JSON data
            $table->string('backup_type')->default('weekly'); // weekly, manual, import
            $table->integer('message_count');
            $table->timestamp('backup_date');
            $table->timestamp('conversation_start_date');
            $table->timestamp('conversation_end_date');
            $table->string('file_name')->nullable(); // For downloaded files
            $table->timestamps();

            $table->index(['user_id', 'backup_date']);
            $table->index(['user_id', 'backup_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_backups');
    }
};
