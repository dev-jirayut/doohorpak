<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->foreignId('rental_id')->constrained()->onDelete('restrict');
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->date('due_date');
            $table->decimal('room_charge', 10, 2)->default(0);
            $table->decimal('electricity_units', 10, 2)->default(0);
            $table->decimal('electricity_rate', 8, 4)->default(0);
            $table->decimal('electricity_charge', 10, 2)->default(0);
            $table->decimal('water_units', 10, 2)->default(0);
            $table->decimal('water_rate', 8, 4)->default(0);
            $table->decimal('water_charge', 10, 2)->default(0);
            $table->decimal('other_charge', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
