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
        // Add wishlist_tracker_code field to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->string('wishlist_tracker_code', 6)->nullable()->unique()->after('issue_tracker_code');
        });

        // Migrate existing tracking tokens from CHEQQ-TRK- to CHEQQ-ISU-
        DB::table('tasks')
            ->where('tracking_token', 'LIKE', 'CHEQQ-TRK-%')
            ->update([
                'tracking_token' => DB::raw("REPLACE(tracking_token, 'CHEQQ-TRK-', 'CHEQQ-ISU-')"),
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert tracking tokens from CHEQQ-ISU- back to CHEQQ-TRK-
        DB::table('tasks')
            ->where('tracking_token', 'LIKE', 'CHEQQ-ISU-%')
            ->update([
                'tracking_token' => DB::raw("REPLACE(tracking_token, 'CHEQQ-ISU-', 'CHEQQ-TRK-')"),
                'updated_at' => now(),
            ]);

        // Drop wishlist_tracker_code field
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['wishlist_tracker_code']);
            $table->dropColumn('wishlist_tracker_code');
        });
    }
};
