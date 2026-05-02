<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('restrict');
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->decimal('electricity_previous', 10, 2)->default(0);
            $table->decimal('electricity_current', 10, 2)->default(0);
            $table->decimal('water_previous', 10, 2)->default(0);
            $table->decimal('water_current', 10, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['room_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
