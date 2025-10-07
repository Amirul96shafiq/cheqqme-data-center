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
            $table->string('spotify_id')->nullable()->after('microsoft_avatar_url');
            $table->string('spotify_avatar_url')->nullable()->after('spotify_id');
            $table->index('spotify_id'); // For faster lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['spotify_id']);
            $table->dropColumn(['spotify_id', 'spotify_avatar_url']);
        });
    }
};
