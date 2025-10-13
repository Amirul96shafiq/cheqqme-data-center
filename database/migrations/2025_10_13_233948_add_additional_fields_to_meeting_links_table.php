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
            $table->json('project_ids')->nullable()->after('client_ids');
            $table->json('important_url_ids')->nullable()->after('document_ids');
            $table->json('user_ids')->nullable()->after('important_url_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_links', function (Blueprint $table) {
            $table->dropColumn(['project_ids', 'important_url_ids', 'user_ids']);
        });
    }
};
