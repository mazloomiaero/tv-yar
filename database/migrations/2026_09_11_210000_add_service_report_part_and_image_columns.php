<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * service_report_parts line fields are named in docs/DATABASE.md narrative
 * (name, quantity, unit_price, total_price). Types follow project standards:
 * quantity DECIMAL(12,3), money DECIMAL(15,2). part_id stays nullable.
 * No is_confirmed — confirmation is the later invoice step.
 *
 * service_report_images has no source dictionary. Storage path + folder kind
 * match docs/ARCHITECTURE.md folders: problems, before, after, damaged-parts.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_report_parts', function (Blueprint $table) {
            $table->string('name', 150)->after('part_id');
            $table->decimal('quantity', 12, 3)->after('name');
            $table->decimal('unit_price', 15, 2)->after('quantity');
            $table->decimal('total_price', 15, 2)->after('unit_price');
        });

        Schema::table('service_report_images', function (Blueprint $table) {
            $table->string('path', 255)->after('service_report_id');
            $table->string('kind', 30)->after('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_report_parts', function (Blueprint $table) {
            $table->dropColumn(['name', 'quantity', 'unit_price', 'total_price']);
        });

        Schema::table('service_report_images', function (Blueprint $table) {
            $table->dropColumn(['path', 'kind']);
        });
    }
};
