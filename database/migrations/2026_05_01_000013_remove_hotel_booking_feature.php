<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove booking_id from invoices first (FK constraint)
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'booking_id')) {
                $table->dropConstrainedForeignId('booking_id');
            }
        });

        Schema::dropIfExists('bookings');

        // Remove hotel type option from properties (just update defaults if needed)
    }

    public function down(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('booking_number', 30)->unique();
            $table->string('guest_name');
            $table->string('guest_phone', 20)->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('nights')->default(1);
            $table->decimal('daily_rate', 10, 2);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('reserved');
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('rental_id')->constrained()->nullOnDelete();
        });
    }
};
