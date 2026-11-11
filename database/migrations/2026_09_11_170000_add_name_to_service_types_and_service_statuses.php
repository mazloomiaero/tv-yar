<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identifier column for service type and service status catalogs.
 *
 * Source has no field dictionary for service_types/service_statuses. Admin
 * ServiceType/ServiceStatus screens need a display identifier. Sample status
 * labels are in docs/ARCHITECTURE.md (main + side). No code/color/order/
 * is_terminal columns.
 *
 * Pivot uniqueness prevents duplicate device-type or status mappings for the
 * same service type (link tables, CASCADE already migrated).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->string('name', 150)->after('id');
        });

        Schema::table('service_statuses', function (Blueprint $table) {
            $table->string('name', 150)->after('id');
        });

        Schema::table('service_type_device_types', function (Blueprint $table) {
            $table->unique(['service_type_id', 'device_type_id']);
        });

        Schema::table('service_type_statuses', function (Blueprint $table) {
            $table->unique(['service_type_id', 'service_status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_type_statuses', function (Blueprint $table) {
            $table->dropUnique(['service_type_id', 'service_status_id']);
        });

        Schema::table('service_type_device_types', function (Blueprint $table) {
            $table->dropUnique(['service_type_id', 'device_type_id']);
        });

        Schema::table('service_statuses', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
