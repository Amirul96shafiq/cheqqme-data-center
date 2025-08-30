<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove any duplicate (user_id, conversation_id) pairs to satisfy the upcoming composite unique index
        $rows = DB::table('chatbot_conversations')
            ->orderBy('id')
            ->get(['id', 'user_id', 'conversation_id']);

        $seen = [];
        $toDelete = [];
        foreach ($rows as $row) {
            $key = $row->user_id.'|'.$row->conversation_id;
            if (isset($seen[$key])) {
                $toDelete[] = $row->id;
            } else {
                $seen[$key] = true;
            }
        }

        if (! empty($toDelete)) {
            DB::table('chatbot_conversations')->whereIn('id', $toDelete)->delete();
        }

        // Drop old constraint (if it exists) and add a regular index instead of unique
        // Use raw SQL to ensure compatibility with SQLite
        DB::statement('DROP INDEX IF EXISTS chatbot_conversations_conversation_id_unique');
        DB::statement('DROP INDEX IF EXISTS chatbot_conversations_user_id_conversation_id_unique');
        DB::statement('CREATE INDEX chatbot_conversations_user_id_conversation_id_index ON chatbot_conversations (user_id, conversation_id)');
    }

    public function down(): void
    {
        // Do not attempt to revert data deletions; just drop the composite index if it exists
        DB::statement('DROP INDEX IF EXISTS chatbot_conversations_user_id_conversation_id_unique');
    }
};
