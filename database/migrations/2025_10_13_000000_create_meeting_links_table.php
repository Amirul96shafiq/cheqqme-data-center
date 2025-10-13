<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_links', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('client_ids')->nullable();
            $table->json('project_ids')->nullable();
            $table->json('document_ids')->nullable();
            $table->json('important_url_ids')->nullable();
            $table->json('user_ids')->nullable();
            $table->string('meeting_platform');
            $table->text('meeting_url')->nullable();
            $table->string('meeting_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('extra_information')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_links');
    }
};
