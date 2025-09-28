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
        Schema::table('tasks', function (Blueprint $table) {
            // Primary indexes for kanban board queries (Trello-style optimization)
            $table->index(['status', 'order_column'], 'idx_tasks_status_order');
            $table->index(['status', 'created_at'], 'idx_tasks_status_created');
            $table->index('order_column', 'idx_tasks_order_column');
            $table->index('created_at', 'idx_tasks_created_at');
            $table->index('due_date', 'idx_tasks_due_date');
        });

        // Optimize comments table for count queries
        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                if (! Schema::hasIndex('comments', 'idx_comments_task_id')) {
                    $table->index('task_id', 'idx_comments_task_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('idx_tasks_status_order');
            $table->dropIndex('idx_tasks_status_created');
            $table->dropIndex('idx_tasks_order_column');
            $table->dropIndex('idx_tasks_created_at');
            $table->dropIndex('idx_tasks_due_date');
        });

        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                if (Schema::hasIndex('comments', 'idx_comments_task_id')) {
                    $table->dropIndex('idx_comments_task_id');
                }
            });
        }
    }
};
