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
        Schema::table('comments', function (Blueprint $table) {
            // Add composite index for efficient querying of task comments
            // This optimizes queries like: WHERE task_id = ? AND parent_id IS NULL ORDER BY created_at DESC
            $table->index(['task_id', 'parent_id', 'created_at'], 'idx_comments_task_parent_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('idx_comments_task_parent_created');
        });
    }
};
