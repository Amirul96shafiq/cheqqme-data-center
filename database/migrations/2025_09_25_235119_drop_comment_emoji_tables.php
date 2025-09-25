<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('comment_emoji_reactions');
        Schema::dropIfExists('comment_emojis');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate comment_emoji_reactions table
        Schema::create('comment_emoji_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('emoji', 10);
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
        });

        // Recreate comment_emojis table
        Schema::create('comment_emojis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('emoji', 10);
            $table->timestamps();

            $table->index(['comment_id', 'emoji']);
            $table->unique(['comment_id', 'user_id']);
        });
    }
};
