<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unique request_number (source: unique request number) and status-history
 * payload needed to record which status was written and which user changed it.
 *
 * Source table service_status_history has no field dictionary. These two
 * columns are the minimum to implement ChangeServiceStatus / status history
 * (docs/CONTRACTS.md, docs/domains/service-request.md). No reason, comment,
 * or workflow-engine columns.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->unique('request_number');
        });

        Schema::table('service_status_history', function (Blueprint $table) {
            $table->foreignId('service_status_id')
                ->after('service_request_id')
                ->constrained('service_statuses')
                ->restrictOnDelete();
            $table->foreignId('changed_by')
                ->nullable()
                ->after('service_status_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_status_history', function (Blueprint $table) {
            $table->dropConstrainedForeignId('changed_by');
            $table->dropConstrainedForeignId('service_status_id');
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropUnique(['request_number']);
        });
    }
};
