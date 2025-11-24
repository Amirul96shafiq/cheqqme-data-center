<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', ['online', 'offline']);
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->string('location_address')->nullable();
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
            $table->string('location_place_id')->nullable();
            $table->foreignId('meeting_link_id')->nullable()->constrained('meeting_links')->cascadeOnDelete();
            $table->string('featured_image')->nullable();
            $table->enum('featured_image_source', ['manual', 'places_api'])->nullable();
            $table->json('invited_user_ids')->nullable();
            $table->json('project_ids')->nullable();
            $table->json('document_ids')->nullable();
            $table->json('important_url_ids')->nullable();
            $table->string('google_calendar_event_id')->nullable();
            $table->boolean('synced_to_calendar')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('event_type');
            $table->index('start_datetime');
            $table->index('end_datetime');
            $table->index('meeting_link_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
