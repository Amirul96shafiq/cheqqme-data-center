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
        Schema::table('users', function (Blueprint $table) {
            // Track how location/timezone was set
            $table->enum('location_source', ['manual', 'auto', 'greeting_modal'])->default('auto')->after('location_updated_at');
            $table->enum('timezone_source', ['manual', 'auto', 'greeting_modal'])->default('auto')->after('timezone');

            // Track if user has explicitly set preferences
            $table->boolean('location_manually_set')->default(false)->after('location_source');
            $table->boolean('timezone_manually_set')->default(false)->after('timezone_source');

            // Track last auto-update timestamp
            $table->timestamp('last_auto_location_update')->nullable()->after('location_manually_set');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'location_source',
                'timezone_source',
                'location_manually_set',
                'timezone_manually_set',
                'last_auto_location_update',
            ]);
        });
    }
};
