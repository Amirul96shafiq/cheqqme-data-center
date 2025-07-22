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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // Client Personal details
            $table->string('pic_name');
            $table->string('pic_email');
            $table->string('pic_contact_number');

            // Client Company details
            $table->string('company_name');
            $table->string('company_email')->nullable();
            $table->string('company_address')->nullable();
            $table->string('billing_address')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Enable trash/restore
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
