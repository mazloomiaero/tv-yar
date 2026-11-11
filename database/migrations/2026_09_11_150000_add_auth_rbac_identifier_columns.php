<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identifier columns required for documented auth/RBAC.
 *
 * Source has no field dictionary for users/roles/permissions.
 * CONTRACTS.md names six roles and sample permissions; internal login is username/password.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('name')->unique()->after('id');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('name')->unique()->after('id');
        });

        Schema::table('user_roles', function (Blueprint $table) {
            $table->unique(['user_id', 'role_id']);
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->unique(['role_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropUnique(['role_id', 'permission_id']);
        });

        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'role_id']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
