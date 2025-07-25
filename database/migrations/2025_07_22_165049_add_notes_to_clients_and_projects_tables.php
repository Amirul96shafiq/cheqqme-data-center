<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('billing_address');
        });

        Schema::table('projects', function (Blueprint $table) {
           // $table->text('notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('projects', function (Blueprint $table) {
           //$table->dropColumn('notes');
        });
    }
};
