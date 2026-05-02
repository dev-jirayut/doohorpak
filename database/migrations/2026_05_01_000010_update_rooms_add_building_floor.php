<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('building_id')->nullable()->after('property_id')->constrained()->nullOnDelete();
            $table->foreignId('floor_id')->nullable()->after('building_id')->constrained()->nullOnDelete();
            $table->decimal('rent_price', 10, 2)->nullable()->after('room_type_id');
            $table->string('electricity_type')->default('unit')->after('rent_price'); // unit, flat
            $table->decimal('electricity_rate', 8, 2)->nullable()->after('electricity_type');
            $table->string('water_type')->default('unit')->after('electricity_rate'); // unit, flat
            $table->decimal('water_rate', 8, 2)->nullable()->after('water_type');
            $table->boolean('has_internet')->default(false)->after('water_rate');
            $table->decimal('internet_fee', 8, 2)->nullable()->after('has_internet');
            $table->boolean('has_parking')->default(false)->after('internet_fee');
            $table->decimal('parking_fee', 8, 2)->nullable()->after('has_parking');
            $table->json('images')->nullable()->after('parking_fee');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['building_id', 'floor_id']);
            $table->dropColumn(['building_id', 'floor_id', 'rent_price', 'electricity_type', 'electricity_rate', 'water_type', 'water_rate', 'has_internet', 'internet_fee', 'has_parking', 'parking_fee', 'images']);
        });
    }
};
