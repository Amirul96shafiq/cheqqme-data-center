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
        Schema::table('meeting_links', function (Blueprint $table) {
            $table->dateTime('meeting_start_time')->nullable()->after('meeting_id');
            $table->integer('meeting_duration')->default(60)->after('meeting_start_time')->comment('Duration in minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_links', function (Blueprint $table) {
            $table->dropColumn(['meeting_start_time', 'meeting_duration']);
        });
    }
};
