<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Value storage columns are not in the source dictionary. Real FKs only (ARCHITECTURE.md).
     */
    public function up(): void
    {
        Schema::create('device_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->restrictOnDelete();
            $table->foreignId('custom_field_id')->constrained('custom_fields')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_custom_field_values');
    }
};
