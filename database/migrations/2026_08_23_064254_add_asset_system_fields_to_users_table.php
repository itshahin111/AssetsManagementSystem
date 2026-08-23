<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('employee_id', 30)->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['employee_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['employee_id', 'phone', 'status']);
        });
    }
};
