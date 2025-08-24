<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('openai_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
      $table->string('conversation_id')->nullable();
      $table->string('model', 100)->nullable();
      $table->string('endpoint')->nullable();
      $table->json('request_payload')->nullable();
      $table->text('response_text')->nullable();
      $table->integer('status_code')->nullable();
      $table->unsignedBigInteger('duration_ms')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('openai_logs');
  }
};


