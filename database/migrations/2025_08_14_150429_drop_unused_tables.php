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
        // Drop unused tables if they exist
        foreach (['colleagues'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        // Note: We are intentionally NOT dropping framework tables like
        // 'sessions', 'jobs', 'job_batches', or 'failed_jobs' since they are
        // created by core migrations and may be needed if configuration changes
        // (e.g., switching queue/session drivers). Keeping them is safe and
        // avoids future migration churn.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate only what we explicitly dropped in up()
        if (! Schema::hasTable('colleagues')) {
            Schema::create('colleagues', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->string('password');
                $table->rememberToken();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }
};
