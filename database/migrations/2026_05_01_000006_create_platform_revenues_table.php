<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('omise_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // percentage_fee, package_fee
            $table->decimal('gross_amount', 12, 2); // total paid by tenant
            $table->decimal('fee_amount', 12, 2);   // platform cut
            $table->decimal('net_amount', 12, 2);   // owner receives
            $table->string('status')->default('pending'); // pending, transferred
            $table->timestamp('transferred_at')->nullable();
            $table->string('transfer_ref')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_revenues');
    }
};
