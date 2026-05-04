<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_webhook_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type')->nullable();
            $table->string('webhook_event_id')->nullable();
            $table->string('line_user_id', 64)->nullable();
            $table->string('status', 30)->default('received');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('message')->nullable();
            $table->string('method', 10)->nullable();
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('signature')->nullable();
            $table->boolean('signature_valid')->nullable();
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index(['webhook_event_id', 'created_at']);
            $table->index(['line_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_webhook_log');
    }
};
