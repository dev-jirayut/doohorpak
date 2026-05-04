<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('line_settings', function (Blueprint $table) {
            $table->string('webhook_url', 500)->nullable()->after('admin_line_user_ids');
        });
    }

    public function down(): void
    {
        Schema::table('line_settings', function (Blueprint $table) {
            $table->dropColumn('webhook_url');
        });
    }
};
