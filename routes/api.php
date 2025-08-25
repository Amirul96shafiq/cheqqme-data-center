<?php

use App\Http\Controllers\OpenaiLogController;
use Illuminate\Support\Facades\Route;

// OpenAI logs API endpoints protected by Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/openai-logs', [OpenaiLogController::class, 'apiIndex'])->name('api.openai.logs');
});
