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
        Schema::create('company', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('trade_name', 150);
            $table->string('logo', 255);
            $table->string('phone', 30);
            $table->string('mobile', 30);
            $table->string('email', 150);
            $table->string('website', 255);
            $table->string('province', 100);
            $table->string('city', 100);
            $table->text('address');
            $table->string('postal_code', 20);
            $table->string('invoice_title', 255);
            $table->text('invoice_description');
            $table->string('primary_color', 20);
            $table->string('secondary_color', 20);
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
