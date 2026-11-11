<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * device_receptions concerns are named in docs/DATABASE.md without a typed
 * dictionary. ReceiveDevice/DeliverDevice still need those fields. Types
 * follow the rest of the schema (VARCHAR/TEXT/DATE). Actor FK SET NULL.
 * No shelf, bin, or stock columns. PDF path is derived from receipt_number.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('device_receptions', function (Blueprint $table) {
            $table->string('receipt_number', 40)->after('id');
            $table->foreignId('customer_id')->after('service_request_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('device_id')->after('customer_id')->constrained('devices')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->after('device_id')->constrained('users')->nullOnDelete();
            $table->date('received_date')->after('received_by');
            $table->text('appearance')->after('received_date');
            $table->text('accompanying_items')->after('appearance');
            $table->text('reason')->after('accompanying_items');
            $table->text('notes')->after('reason');
            $table->date('delivery_date')->nullable()->after('notes');
            $table->unique('receipt_number');
            $table->unique('service_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_receptions', function (Blueprint $table) {
            $table->dropUnique(['receipt_number']);
            $table->dropUnique(['service_request_id']);
            $table->dropConstrainedForeignId('customer_id');
            $table->dropConstrainedForeignId('device_id');
            $table->dropConstrainedForeignId('received_by');
            $table->dropColumn([
                'receipt_number',
                'received_date',
                'appearance',
                'accompanying_items',
                'reason',
                'notes',
                'delivery_date',
            ]);
        });
    }
};
