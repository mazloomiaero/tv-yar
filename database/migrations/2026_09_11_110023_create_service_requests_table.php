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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 40);
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('device_id')->constrained('devices')->restrictOnDelete();
            $table->foreignId('service_type_id')->constrained('service_types')->restrictOnDelete();
            $table->foreignId('address_id')->constrained('customer_addresses')->restrictOnDelete();
            $table->foreignId('status_id')->constrained('service_statuses')->restrictOnDelete();
            $table->string('source', 30);
            $table->string('priority', 20);
            $table->text('problem_description');
            $table->date('preferred_date');
            $table->time('preferred_start_time');
            $table->time('preferred_end_time');
            $table->text('notes');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
