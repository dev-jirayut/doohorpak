<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('electricity_rate', 8, 4)->comment('ราคาค่าไฟต่อหน่วย (บาท)');
            $table->decimal('water_rate', 8, 4)->comment('ราคาค่าน้ำต่อหน่วย (บาท)');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_rates');
    }
};
