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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 40);
            $table->foreignId('service_request_id')->constrained('service_requests')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('customer_name', 200);
            $table->string('customer_mobile', 30);
            $table->text('customer_address');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2);
            $table->decimal('tax', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('status', 30);
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
