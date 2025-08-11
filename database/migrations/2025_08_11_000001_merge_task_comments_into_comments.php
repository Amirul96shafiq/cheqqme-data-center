<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
  public function up(): void
  {
    if (!Schema::hasTable('task_comments')) {
      return; // nothing to do
    }

    // Ensure target comments table exists
    if (!Schema::hasTable('comments')) {
      throw new RuntimeException('comments table missing; cannot merge task_comments');
    }

    // Fetch legacy rows
    $rows = DB::table('task_comments')->get();
    foreach ($rows as $row) {
      // Avoid naive duplicates: skip if an identical comment already exists for same task & user & body
      $exists = DB::table('comments')
        ->where('task_id', $row->task_id)
        ->where('user_id', $row->user_id)
        ->where('comment', $row->body)
        ->exists();
      if ($exists) {
        continue;
      }
      DB::table('comments')->insert([
        'task_id' => $row->task_id,
        'user_id' => $row->user_id,
        'comment' => $row->body,
        'created_at' => $row->created_at,
        'updated_at' => $row->updated_at,
        'deleted_at' => $row->deleted_at ?? null,
      ]);
    }

    Schema::drop('task_comments');
  }

  public function down(): void
  {
    // Recreate the legacy table (cannot reliably reverse which rows came from it)
    if (!Schema::hasTable('task_comments')) {
      Schema::create('task_comments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->text('body');
        $table->timestamps();
        $table->softDeletes();
      });
    }
    // Optional: We do NOT attempt to move data back (irreversible merge)
  }
};
