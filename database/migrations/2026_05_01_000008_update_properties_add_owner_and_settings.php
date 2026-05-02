<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('bank_account_name')->nullable()->after('phone');
            $table->string('bank_account_number')->nullable()->after('bank_account_name');
            $table->string('bank_name')->nullable()->after('bank_account_number');
            $table->string('promptpay_id')->nullable()->after('bank_name');
            $table->string('qr_payment_image')->nullable()->after('promptpay_id');
            // Revenue model
            $table->string('revenue_model')->default('percentage')->after('type'); // percentage, package
            $table->decimal('revenue_percentage', 5, 2)->default(5.00)->after('revenue_model');
            $table->decimal('revenue_package_per_room', 10, 2)->default(50.00)->after('revenue_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn(['owner_id', 'bank_account_name', 'bank_account_number', 'bank_name', 'promptpay_id', 'qr_payment_image', 'revenue_model', 'revenue_percentage', 'revenue_package_per_room']);
        });
    }
};
