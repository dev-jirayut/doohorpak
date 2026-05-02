<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('tenant_id_card_copy_path')->nullable()->after('file_path');
            $table->string('paper_contract_image_path')->nullable()->after('tenant_id_card_copy_path');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['tenant_id_card_copy_path', 'paper_contract_image_path']);
        });
    }
};
