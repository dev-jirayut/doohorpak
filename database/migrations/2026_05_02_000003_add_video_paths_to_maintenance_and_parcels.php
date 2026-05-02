<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('video_path')->nullable()->after('image_path');
        });

        Schema::table('parcels', function (Blueprint $table) {
            $table->string('video_path')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn('video_path');
        });

        Schema::table('parcels', function (Blueprint $table) {
            $table->dropColumn('video_path');
        });
    }
};
