<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Feedback payload named in the architecture as «امتیاز و نظر» (score and comment).
     * Notifications table is not Laravel's morph schema; persist only a name identifier.
     */
    public function up(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->string('score', 50)->after('service_request_id');
            $table->text('comment')->nullable()->after('score');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->string('name', 150)->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropColumn(['score', 'comment']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
