<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Image path/type columns are not in the source dictionary.
     */
    public function up(): void
    {
        Schema::create('service_report_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_report_id')->constrained('service_reports')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_report_images');
    }
};
