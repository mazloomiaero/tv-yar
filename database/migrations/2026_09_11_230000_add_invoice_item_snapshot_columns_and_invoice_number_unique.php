<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Invoice item snapshot fields (narrative names in docs/DATABASE.md) and unique
     * invoice_number (source: unique document numbers).
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('invoice_number');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('title', 150)->after('invoice_id');
            $table->text('description')->after('title');
            $table->decimal('quantity', 12, 3)->after('description');
            $table->decimal('unit_price', 15, 2)->after('quantity');
            $table->decimal('total', 15, 2)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'quantity', 'unit_price', 'total']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
        });
    }
};
