<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One invoice per service request.
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicateCount = (int) \Illuminate\Support\Facades\DB::table('invoices')
            ->select('service_request_id')
            ->groupBy('service_request_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        // Existing production duplicates cannot be physically deleted (financial history).
        // CreateInvoice still enforces one-active-invoice at the application layer.
        if ($duplicateCount > 0) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('service_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['service_request_id']);
        });
    }
};
