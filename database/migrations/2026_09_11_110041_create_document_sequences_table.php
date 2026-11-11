<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Column names are sourced; VARCHAR/INT widths are not. string() defaults to 255; year and current_number are unsigned integers.
     */
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->string('prefix');
            $table->unsignedInteger('year');
            $table->unsignedInteger('current_number');
            $table->timestamps();

            $table->unique(['document_type', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
