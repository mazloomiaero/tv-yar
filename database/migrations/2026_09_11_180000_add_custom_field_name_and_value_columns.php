<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identifier and stored-value columns for custom-field tables.
 *
 * Source has no field dictionary for these tables (docs/DATABASE.md).
 * Admin Custom Field screens need a catalog identifier. Select options are
 * the purpose of custom_field_options (docs/DATABASE.md table catalog).
 * Device vs request values are stored in two tables with real FKs
 * (docs/ARCHITECTURE.md; docs/domains/custom-fields.md). No type, required,
 * sort_order, or JSON columns. Select vs free text is inferred from whether
 * a field has options.
 *
 * Pivot uniqueness prevents duplicate mappings (link tables, CASCADE already
 * migrated). Value uniqueness is one stored value per field per device/request.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            if (! Schema::hasColumn('custom_fields', 'name')) {
                $table->string('name', 150)->after('id');
            }
        });

        Schema::table('custom_field_options', function (Blueprint $table) {
            if (! Schema::hasColumn('custom_field_options', 'name')) {
                $table->string('name', 150)->after('custom_field_id');
            }
            $table->unique(['custom_field_id', 'name'], 'cf_options_field_name_unique');
        });

        Schema::table('device_custom_field_values', function (Blueprint $table) {
            if (! Schema::hasColumn('device_custom_field_values', 'value')) {
                $table->string('value', 255)->after('custom_field_id');
            }
            $table->unique(['device_id', 'custom_field_id'], 'dcfv_device_field_unique');
        });

        Schema::table('service_request_custom_field_values', function (Blueprint $table) {
            if (! Schema::hasColumn('service_request_custom_field_values', 'value')) {
                $table->string('value', 255)->after('custom_field_id');
            }
            $table->unique(['service_request_id', 'custom_field_id'], 'srcfv_request_field_unique');
        });

        Schema::table('device_type_custom_fields', function (Blueprint $table) {
            $table->unique(['device_type_id', 'custom_field_id'], 'dtcf_type_field_unique');
        });

        Schema::table('service_type_custom_fields', function (Blueprint $table) {
            $table->unique(['service_type_id', 'custom_field_id'], 'stcf_type_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_type_custom_fields', function (Blueprint $table) {
            $table->dropUnique('stcf_type_field_unique');
        });

        Schema::table('device_type_custom_fields', function (Blueprint $table) {
            $table->dropUnique('dtcf_type_field_unique');
        });

        Schema::table('service_request_custom_field_values', function (Blueprint $table) {
            $table->dropUnique('srcfv_request_field_unique');
            $table->dropColumn('value');
        });

        Schema::table('device_custom_field_values', function (Blueprint $table) {
            $table->dropUnique('dcfv_device_field_unique');
            $table->dropColumn('value');
        });

        Schema::table('custom_field_options', function (Blueprint $table) {
            $table->dropUnique('cf_options_field_name_unique');
            $table->dropColumn('name');
        });

        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
