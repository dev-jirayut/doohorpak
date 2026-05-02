<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('draft'); // draft, active, expired, terminated
            $table->string('file_path')->nullable();
            $table->string('tenant_signature')->nullable();
            $table->string('owner_signature')->nullable();
            $table->timestamp('tenant_signed_at')->nullable();
            $table->timestamp('owner_signed_at')->nullable();
            $table->boolean('reminder_30_sent')->default(false);
            $table->boolean('reminder_7_sent')->default(false);
            $table->text('terms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
