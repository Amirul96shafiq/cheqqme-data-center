<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the unique index that prevents multiple messages per conversation
        DB::statement('DROP INDEX IF EXISTS chatbot_conversations_user_id_conversation_id_unique');

        // Create a regular index for performance (allows multiple messages per conversation)
        DB::statement('CREATE INDEX IF NOT EXISTS chatbot_conversations_user_id_conversation_id_index ON chatbot_conversations (user_id, conversation_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the regular index
        DB::statement('DROP INDEX IF EXISTS chatbot_conversations_user_id_conversation_id_index');

        // Recreate the unique index (this will fail if there are duplicate records)
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS chatbot_conversations_user_id_conversation_id_unique ON chatbot_conversations (user_id, conversation_id)');
    }
};
