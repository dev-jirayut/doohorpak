<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->string('parcel_number')->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('parcel'); // parcel, letter, document, food
            $table->string('sender')->nullable();
            $table->string('carrier')->nullable(); // EMS, Kerry, Flash, J&T, etc.
            $table->string('tracking_number')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('waiting'); // waiting, notified, collected, returned
            $table->string('received_by')->nullable(); // staff name who received
            $table->timestamp('received_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->string('image_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
