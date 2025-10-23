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
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2); // ISO country code (MY, US, SG, etc.)
            $table->string('name'); // Holiday name
            $table->date('date'); // Holiday date
            $table->string('type')->default('national'); // national, regional, religious
            $table->boolean('is_recurring')->default(false); // For holidays that repeat annually
            $table->json('localized_names')->nullable(); // For multi-language support
            $table->timestamps();

            $table->index(['country_code', 'date']);
            $table->unique(['country_code', 'name', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_holidays');
    }
};
