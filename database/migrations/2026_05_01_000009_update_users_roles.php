<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // role column already exists, just ensure it supports all values
        // super_admin, owner, staff, tenant
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('line_user_id')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('line_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'line_user_id', 'avatar']);
        });
    }
};
