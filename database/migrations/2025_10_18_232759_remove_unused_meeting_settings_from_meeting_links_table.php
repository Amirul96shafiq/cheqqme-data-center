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
            $table->dropColumn([
                'mute_attendees',
                'allow_attendees_screen_share',
                'allow_record_meeting',
                'allow_transcript_meeting',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_links', function (Blueprint $table) {
            $table->boolean('mute_attendees')->default(false)->after('meeting_passcode');
            $table->boolean('allow_attendees_screen_share')->default(true)->after('mute_attendees');
            $table->boolean('allow_record_meeting')->default(false)->after('allow_attendees_screen_share');
            $table->boolean('allow_transcript_meeting')->default(false)->after('allow_record_meeting');
        });
    }
};
