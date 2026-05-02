<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('line_settings', function (Blueprint $table) {
            $table->json('admin_line_user_ids')->nullable()->after('oa_channel_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('line_settings', function (Blueprint $table) {
            $table->dropColumn('admin_line_user_ids');
        });
    }
};
