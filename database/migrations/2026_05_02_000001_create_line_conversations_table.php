<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('line_user_id', 64);
            $table->string('display_name')->nullable();
            $table->string('picture_url')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('chat_name')->nullable(); // custom override label
            $table->timestamp('last_message_at')->nullable();
            $table->boolean('has_unread')->default(false);
            $table->timestamps();

            $table->unique(['property_id', 'line_user_id']);
            $table->index(['property_id', 'last_message_at']);
        });

        Schema::create('line_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('line_conversations')->cascadeOnDelete();
            $table->string('line_message_id', 64)->nullable()->unique();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('type')->default('text'); // text, image, sticker, etc.
            $table->text('content')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_messages');
        Schema::dropIfExists('line_conversations');
    }
};
