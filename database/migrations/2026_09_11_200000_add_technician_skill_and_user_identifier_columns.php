<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * skills and technicians have no source field dictionary. Admin catalog and
 * TechnicianSuggestionService still need a name identifier. technicians.user_id
 * is optional so the Technician panel can list assignments for the internal login.
 * No phone or employee_code columns.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->string('name', 150)->after('id');
        });

        Schema::table('technicians', function (Blueprint $table) {
            $table->string('name', 150)->after('id');
            $table->foreignId('user_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('technician_skills', function (Blueprint $table) {
            $table->unique(['technician_id', 'skill_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_skills', function (Blueprint $table) {
            $table->dropUnique(['technician_id', 'skill_id']);
        });

        Schema::table('technicians', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('name');
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
