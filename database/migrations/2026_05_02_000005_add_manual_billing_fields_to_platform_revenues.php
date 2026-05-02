<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_revenues', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('omise_transaction_id')->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->after('invoice_id')->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('billing_month')->nullable()->after('payment_id');
            $table->unsignedSmallInteger('billing_year')->nullable()->after('billing_month');
            $table->string('payment_channel')->default('online')->after('type');
            $table->text('note')->nullable()->after('transfer_ref');
        });
    }

    public function down(): void
    {
        Schema::table('platform_revenues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropConstrainedForeignId('payment_id');
            $table->dropColumn(['billing_month', 'billing_year', 'payment_channel', 'note']);
        });
    }
};
