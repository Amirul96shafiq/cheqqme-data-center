<?php

// Quick scanner for '<blockquote' occurrences across all text-like columns in the SQLite DB.
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
// Boot framework (runs service providers)
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$connection = DB::connection();
$driver = $connection->getDriverName();
if ($driver !== 'sqlite') {
    echo "[WARN] This script is tailored for sqlite; current driver: {$driver}\n";
}

$tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))
    ->pluck('name')
    ->filter(fn ($t) => ! in_array($t, ['migrations']))
    ->values();

$pattern = '<blockquote';
$results = [];
$totalOccurrences = 0;
foreach ($tables as $table) {
    $cols = DB::select("PRAGMA table_info({$table})");
    $textCols = collect($cols)
        ->filter(fn ($c) => preg_match('/TEXT|CHAR|CLOB/i', $c->type))
        ->pluck('name');
    foreach ($textCols as $col) {
        $count = DB::table($table)->where($col, 'like', "%$pattern%")->count();
        if ($count > 0) {
            $results[] = [
                'table' => $table,
                'column' => $col,
                'count' => $count,
            ];
            $totalOccurrences += $count;
        }
    }
}

if (empty($results)) {
    echo "No <blockquote> occurrences found.\n";
    exit(0);
}

echo "Found <blockquote> occurrences:\n";
foreach ($results as $row) {
    echo "- {$row['table']}.{$row['column']}: {$row['count']}\n";
}

echo "TOTAL: {$totalOccurrences}\n";

// Optional remediation hint
// php scripts/remove_blockquotes.php (could be added) to strip tags while keeping inner content.
