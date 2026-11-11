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

        $duplicateActiveMobiles = (int) DB::table('customers')
            ->whereNull('deleted_at')
            ->select('mobile')
            ->groupBy('mobile')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        // Existing duplicates stay (soft-delete history). Form Requests still enforce uniqueness.
        if ($duplicateActiveMobiles > 0) {
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement(
                'CREATE UNIQUE INDEX customers_mobile_active_unique ON customers (mobile) WHERE deleted_at IS NULL'
            );

            return;
        }

        // MySQL 8.0.13+ functional indexes. Older MariaDB builds reject the expression syntax.
        try {
            DB::statement(
                'CREATE UNIQUE INDEX customers_mobile_active_unique ON customers ((CASE WHEN `deleted_at` IS NULL THEN `mobile` ELSE NULL END))'
            );
        } catch (\Throwable $exception) {
            // Application-level unique rules remain the source of truth on hosts without functional indexes.
        }
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
