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
            $table->string('github_id')->nullable()->after('microsoft_refresh_token');
            $table->text('github_avatar_url')->nullable()->after('github_id');
            $table->text('github_token')->nullable()->after('github_avatar_url');
            $table->text('github_refresh_token')->nullable()->after('github_token');
            $table->timestamp('github_connected_at')->nullable()->after('github_refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'github_id',
                'github_avatar_url',
                'github_token',
                'github_refresh_token',
                'github_connected_at',
            ]);
        });
    }
};
