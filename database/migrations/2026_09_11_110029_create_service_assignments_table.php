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
        Schema::create('service_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->restrictOnDelete();
            $table->foreignId('technician_id')->constrained('technicians')->restrictOnDelete();
            $table->boolean('is_suggested');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('scheduled_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status', 30);
            $table->string('technician_response', 30);
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_assignments');
    }
};
