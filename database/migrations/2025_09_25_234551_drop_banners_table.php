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
        Schema::dropIfExists('banners');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->json('data');
            $table->timestamps();
        });
    }
};
