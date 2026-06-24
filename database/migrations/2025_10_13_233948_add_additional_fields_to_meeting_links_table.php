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
            if (! Schema::hasColumn('meeting_links', 'project_ids')) {
                $table->json('project_ids')->nullable()->after('client_ids');
            }
            if (! Schema::hasColumn('meeting_links', 'important_url_ids')) {
                $table->json('important_url_ids')->nullable()->after('document_ids');
            }
            if (! Schema::hasColumn('meeting_links', 'user_ids')) {
                $table->json('user_ids')->nullable()->after('important_url_ids');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_links', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('meeting_links', 'project_ids')) {
                $columnsToDrop[] = 'project_ids';
            }
            if (Schema::hasColumn('meeting_links', 'important_url_ids')) {
                $columnsToDrop[] = 'important_url_ids';
            }
            if (Schema::hasColumn('meeting_links', 'user_ids')) {
                $columnsToDrop[] = 'user_ids';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
