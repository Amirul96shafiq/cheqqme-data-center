<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('tasks', function (Blueprint $table) {
      // Store selected Important URL ids as a JSON array, consistent with 'project' and 'document'
      $table->json('important_url')->nullable()->after('document');
    });

    // Backfill existing tasks based on their client → projects → important_urls
    // We intentionally use the query builder for portability across DBs (incl. SQLite).
    try {
      // Only attempt backfill if the requisite tables/columns exist
      if (
        Schema::hasTable('tasks') &&
        Schema::hasTable('projects') &&
        Schema::hasTable('important_urls') &&
        Schema::hasColumn('tasks', 'client') &&
        Schema::hasColumn('tasks', 'important_url') &&
        Schema::hasColumn('projects', 'client_id') &&
        Schema::hasColumn('important_urls', 'project_id')
      ) {
        // Process tasks in chunks to avoid memory issues
        $lastId = 0;
        while (true) {
          $tasks = DB::table('tasks')
            ->whereNotNull('client')
            ->where('id', '>', $lastId)
            ->orderBy('id')
            ->limit(500)
            ->get(['id', 'client']);

          if ($tasks->isEmpty()) {
            break;
          }

          foreach ($tasks as $task) {
            $importantUrlIds = DB::table('important_urls')
              ->join('projects', 'projects.id', '=', 'important_urls.project_id')
              ->where('projects.client_id', $task->client)
              ->pluck('important_urls.id')
              ->values()
              ->all();

            DB::table('tasks')
              ->where('id', $task->id)
              ->update([
                'important_url' => json_encode($importantUrlIds),
                'updated_at' => now(),
              ]);

            $lastId = $task->id;
          }
        }
      }
    } catch (\Throwable $e) {
      // Swallow backfill errors silently to prevent migration failure; schema change remains applied.
      // Consider manual backfill if needed.
    }
  }

  public function down(): void
  {
    Schema::table('tasks', function (Blueprint $table) {
      $table->dropColumn('important_url');
    });
  }
};
