<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->index(['property_id', 'status', 'start_date'], 'rentals_property_status_start_idx');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->index(['property_id', 'status', 'created_at'], 'contracts_property_status_created_idx');
            $table->index(['rental_id', 'status'], 'contracts_rental_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex('contracts_property_status_created_idx');
            $table->dropIndex('contracts_rental_status_idx');
        });

        Schema::table('rentals', function (Blueprint $table) {
            $table->dropIndex('rentals_property_status_start_idx');
        });
    }
};
