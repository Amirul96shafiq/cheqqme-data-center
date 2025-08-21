<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Ensure users and task exist
    $author = User::firstOrCreate(
        ['email' => 'author@example.com'],
        ['username' => 'author1', 'name' => 'Amirul Shafiq Harun', 'password' => bcrypt('password')]
    );
    $other = User::firstOrCreate(
        ['email' => 'other@example.com'],
        ['username' => 'amirul_other', 'name' => 'Amirul Other Account', 'password' => bcrypt('password')]
    );

    $task = Task::firstOrCreate(
        ['title' => 'Notify Mentions'],
        ['description' => 'Ensure mentions send notifications', 'status' => 'todo']
    );

    // Create a comment with full-name mentions
    $text = 'test @'.$author->name.' self comment and @'.$other->name.' for other mentioned';

    $comment = Comment::create([
        'task_id' => $task->id,
        'user_id' => $author->id,
        'comment' => $text,
        'mentions' => Comment::extractMentions($text),
    ]);

    $comment->processMentions();

    // Output notification info
    echo 'author_count='.$author->notifications()->count().PHP_EOL;
    echo 'other_count='.$other->notifications()->count().PHP_EOL;

    foreach ($author->notifications()->latest()->take(5)->get() as $n) {
        $data = $n->data ?? [];
        $payloadType = is_array($data) ? ($data['type'] ?? '') : '';
        $format = is_array($data) ? ($data['format'] ?? '') : '';
        $commentId = is_array($data) ? ($data['comment_id'] ?? '') : '';
        echo 'author notif: type='.$n->type.' payload_type='.$payloadType.' format='.$format.' comment_id='.$commentId.PHP_EOL;
    }

    foreach ($other->notifications()->latest()->take(5)->get() as $n) {
        $data = $n->data ?? [];
        $payloadType = is_array($data) ? ($data['type'] ?? '') : '';
        $format = is_array($data) ? ($data['format'] ?? '') : '';
        $commentId = is_array($data) ? ($data['comment_id'] ?? '') : '';
        echo 'other notif: type='.$n->type.' payload_type='.$payloadType.' format='.$format.' comment_id='.$commentId.PHP_EOL;
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: '.$e->getMessage().PHP_EOL);
    exit(1);
}
