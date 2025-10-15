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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('google_connected_at')->nullable()->after('google_avatar_url');
            $table->timestamp('microsoft_connected_at')->nullable()->after('microsoft_avatar_url');
            $table->timestamp('spotify_connected_at')->nullable()->after('spotify_avatar_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_connected_at', 'microsoft_connected_at', 'spotify_connected_at']);
        });
    }
};
