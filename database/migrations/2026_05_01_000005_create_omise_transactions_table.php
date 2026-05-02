<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omise_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('omise_charge_id')->unique()->nullable();
            $table->string('omise_source_id')->nullable();
            $table->string('payment_method'); // credit_card, promptpay
            $table->unsignedBigInteger('amount'); // in satang (× 100)
            $table->string('currency')->default('thb');
            $table->string('status')->default('pending'); // pending, successful, failed, expired
            $table->string('failure_code')->nullable();
            $table->string('failure_message')->nullable();
            $table->string('authorize_uri')->nullable(); // for 3DS / PromptPay redirect
            $table->json('metadata')->nullable();
            $table->timestamp('charged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omise_transactions');
    }
};
