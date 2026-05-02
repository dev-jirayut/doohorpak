<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable()->after('id')->constrained()->onDelete('restrict');
            // make rental_id nullable
            $table->foreignId('rental_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_id');
            $table->foreignId('rental_id')->nullable(false)->change();
        });
    }
};
