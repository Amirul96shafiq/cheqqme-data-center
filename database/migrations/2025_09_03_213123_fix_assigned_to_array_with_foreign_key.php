<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop the foreign key constraint
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
        });

        // Migrate existing data: convert single assigned_to to array format
        DB::table('tasks')->orderBy('id')->each(function ($task) {
            $assignedToArray = $task->assigned_to ? [$task->assigned_to] : [];
            DB::table('tasks')
                ->where('id', $task->id)
                ->update(['assigned_to_new' => json_encode($assignedToArray)]);
        });

        // Drop the old column and rename the new one
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('assigned_to_new', 'assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Create temporary column for single value
            $table->integer('assigned_to_old')->nullable()->after('assigned_to');
        });

        // Migrate data back: take first user from array as single assigned_to
        DB::table('tasks')->orderBy('id')->each(function ($task) {
            $assignedToArray = json_decode($task->assigned_to, true) ?? [];
            $firstAssignedTo = ! empty($assignedToArray) ? $assignedToArray[0] : null;

            DB::table('tasks')
                ->where('id', $task->id)
                ->update(['assigned_to_old' => $firstAssignedTo]);
        });

        // Drop the array column and rename the single value column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('assigned_to_old', 'assigned_to');
        });

        // Re-add the foreign key constraint
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }
};
