<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 30)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('mobile', 30);
            $table->string('mobile_secondary', 30);
            $table->string('email', 150);
            $table->string('national_id', 20)->nullable();
            $table->text('notes');
            $table->string('status', 20);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
