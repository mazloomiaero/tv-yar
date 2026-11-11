<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Active customer mobiles must be unique. Soft-deleted rows are left out of
 * the index so a released number can be registered again. MySQL has no
 * partial indexes, so the key is an expression that becomes NULL when
 * deleted_at is set (unique indexes allow many NULLs). SQLite, used by
 * tests, uses a partial unique index on the same predicate.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                'CREATE UNIQUE INDEX customers_mobile_active_unique ON customers ((CASE WHEN `deleted_at` IS NULL THEN `mobile` ELSE NULL END))'
            );

            return;
        }

        DB::statement(
            'CREATE UNIQUE INDEX customers_mobile_active_unique ON customers (mobile) WHERE deleted_at IS NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE customers DROP INDEX customers_mobile_active_unique');

            return;
        }

        DB::statement('DROP INDEX customers_mobile_active_unique');
    }
};
