<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add string priority with limited known values: low, medium, high
            // Default to 'medium' for a sensible middle-ground
            $table->string('priority', 16)->default('medium')->after('status');

            // Optional index if filtering/sorting by priority becomes common
            $table->index('priority', 'idx_tasks_priority');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('idx_tasks_priority');
            $table->dropColumn('priority');
        });
    }
};
