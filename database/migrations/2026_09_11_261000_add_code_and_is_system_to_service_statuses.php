<?php

declare(strict_types=1);

use App\Support\SampleServiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stable status codes so request creation and workshop transitions
 * do not depend on the Persian display name. Seeded rows are system
 * rows and cannot be deleted.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_statuses', function (Blueprint $table) {
            $table->string('code', 64)->nullable()->after('name');
            $table->boolean('is_system')->default(false)->after('code');
        });

        foreach (SampleServiceStatus::definitions() as $definition) {
            $id = DB::table('service_statuses')
                ->whereNull('code')
                ->where('name', $definition['name'])
                ->orderBy('id')
                ->value('id');

            if ($id === null) {
                $id = DB::table('service_statuses')
                    ->whereNull('code')
                    ->where('name', $definition['english'])
                    ->orderBy('id')
                    ->value('id');
            }

            if ($id === null) {
                continue;
            }

            DB::table('service_statuses')->where('id', $id)->update([
                'name' => $definition['name'],
                'code' => $definition['code'],
                'is_system' => true,
            ]);
        }

        Schema::table('service_statuses', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_statuses', function (Blueprint $table) {
            $table->dropUnique(['code']);
        });

        Schema::table('service_statuses', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_system']);
        });
    }
};
