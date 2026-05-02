<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('line_settings', function (Blueprint $table) {
            $table->string('rich_menu_id')->nullable()->after('admin_line_user_ids');
            $table->string('rich_menu_image_path')->nullable()->after('rich_menu_id');
            $table->json('rich_menu_actions')->nullable()->after('rich_menu_image_path');
            $table->timestamp('rich_menu_created_at')->nullable()->after('rich_menu_actions');
        });
    }

    public function down(): void
    {
        Schema::table('line_settings', function (Blueprint $table) {
            $table->dropColumn([
                'rich_menu_id',
                'rich_menu_image_path',
                'rich_menu_actions',
                'rich_menu_created_at',
            ]);
        });
    }
};
