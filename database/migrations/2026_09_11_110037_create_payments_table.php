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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('method', 30);
            $table->string('status', 30);
            $table->dateTime('paid_at')->nullable();
            $table->string('reference_number', 150);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_image', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
