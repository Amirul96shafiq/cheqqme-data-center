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
        Schema::table('events', function (Blueprint $table) {
            // Drop old location fields
            $table->dropColumn(['location_latitude', 'location_longitude']);

            // Rename location_address to location_full_address
            $table->renameColumn('location_address', 'location_full_address');

            // Add new location_title field
            $table->string('location_title')->nullable()->after('location_full_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Reverse the changes
            $table->dropColumn('location_title');

            // Rename back
            $table->renameColumn('location_full_address', 'location_address');

            // Add back latitude and longitude
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
        });
    }
};
