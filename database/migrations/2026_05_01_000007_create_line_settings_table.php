<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('notify_token')->nullable();
            $table->string('oa_channel_id')->nullable();
            $table->string('oa_channel_secret')->nullable();
            $table->string('oa_channel_access_token')->nullable();
            $table->boolean('notify_on_invoice')->default(true);
            $table->boolean('notify_on_overdue')->default(true);
            $table->boolean('notify_on_maintenance')->default(true);
            $table->boolean('notify_on_new_tenant')->default(true);
            $table->time('reminder_time')->default('09:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_settings');
    }
};
