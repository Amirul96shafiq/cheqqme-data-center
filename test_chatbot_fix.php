<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ChatbotConversation;

$conversationId = 'test_'.time();

try {
    // Create user message
    ChatbotConversation::create([
        'user_id' => 1,
        'conversation_id' => $conversationId,
        'role' => 'user',
        'content' => 'Hello',
        'last_activity' => now(),
    ]);

    // Create assistant message
    ChatbotConversation::create([
        'user_id' => 1,
        'conversation_id' => $conversationId,
        'role' => 'assistant',
        'content' => 'Hi there!',
        'last_activity' => now(),
    ]);

    echo 'Success: Both records created for conversation '.$conversationId.PHP_EOL;

    // Check records were created
    $count = ChatbotConversation::where('conversation_id', $conversationId)->count();
    echo 'Records in conversation: '.$count.PHP_EOL;

} catch (Exception $e) {
    echo 'Error: '.$e->getMessage().PHP_EOL;
}
