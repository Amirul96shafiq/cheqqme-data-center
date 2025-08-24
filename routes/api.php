<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenaiLogController;

// OpenAI logs API endpoints protected by Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
  Route::get('/openai-logs', [OpenaiLogController::class, 'apiIndex'])->name('api.openai.logs');
});
