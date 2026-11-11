<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identifier column for device type and brand catalogs.
 *
 * Source has no field dictionary for device_types/brands. Admin DeviceType/Brand
 * controllers need a display identifier. Examples in the source: TV,
 * Air Conditioner, Mobile (docs/domains/custom-fields.md).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('device_types', function (Blueprint $table) {
            $table->string('name', 150)->after('id');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->string('name', 150)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_types', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
