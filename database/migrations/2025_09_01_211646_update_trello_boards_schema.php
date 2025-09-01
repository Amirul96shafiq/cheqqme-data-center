<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Copy data from existing columns to new ones (columns already exist)
        DB::statement('UPDATE trello_boards SET notes = description WHERE notes IS NULL AND description IS NOT NULL');
        DB::statement('UPDATE trello_boards SET show_on_boards = is_active WHERE show_on_boards IS NULL');
        DB::statement('UPDATE trello_boards SET updated_by = created_by WHERE updated_by IS NULL');

        // Drop indexes first (required for SQLite)
        Schema::table('trello_boards', function (Blueprint $table) {
            $table->dropIndex('trello_boards_trello_board_id_index');
            $table->dropUnique('trello_boards_trello_board_id_unique');
            $table->dropIndex('trello_boards_is_active_index');
        });

        // Drop old columns
        Schema::table('trello_boards', function (Blueprint $table) {
            $table->dropColumn([
                'trello_board_id',
                'trello_api_key',
                'trello_api_token',
                'description',
                'board_data',
                'cards_data',
                'last_synced_at',
                'is_active',
                'sync_status',
                'sync_error',
                'sync_job_id',
                'auto_created',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First add back the old columns
        Schema::table('trello_boards', function (Blueprint $table) {
            $table->string('trello_board_id')->nullable()->after('id');
            $table->string('trello_api_key')->nullable()->after('name');
            $table->string('trello_api_token')->nullable()->after('trello_api_key');
            $table->text('description')->nullable()->after('notes');
            $table->text('board_data')->nullable()->after('description');
            $table->text('cards_data')->nullable()->after('board_data');
            $table->datetime('last_synced_at')->nullable()->after('cards_data');
            $table->boolean('is_active')->default(true)->after('show_on_boards');
            $table->string('sync_status')->nullable()->after('is_active');
            $table->text('sync_error')->nullable()->after('sync_status');
            $table->string('sync_job_id')->nullable()->after('sync_error');
            $table->boolean('auto_created')->default(false)->after('sync_job_id');

            // Restore data from new columns to old ones
            DB::statement('UPDATE trello_boards SET description = notes WHERE description IS NULL AND notes IS NOT NULL');
            DB::statement('UPDATE trello_boards SET is_active = show_on_boards WHERE is_active IS NULL');
        });

        // Then drop the new columns
        Schema::table('trello_boards', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'notes',
                'show_on_boards',
                'extra_information',
                'updated_by',
            ]);
        });
    }
};
