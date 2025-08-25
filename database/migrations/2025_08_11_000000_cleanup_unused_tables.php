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
        // Drop 'links' table if it still exists and no model/resource uses it.
        if (Schema::hasTable('links')) {
            Schema::drop('links');
        }

        // Drop legacy pivot 'task_user' if present (design now uses tasks.assigned_to)
        if (Schema::hasTable('task_user')) {
            Schema::drop('task_user');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate minimal 'links' schema (without created_by foreign key to keep rollback simple)
        if (! Schema::hasTable('links')) {
            Schema::create('links', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('url');
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Recreate legacy pivot if needed (generic structure)
        if (! Schema::hasTable('task_user')) {
            Schema::create('task_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }
};
