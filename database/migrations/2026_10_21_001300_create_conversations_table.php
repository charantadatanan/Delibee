<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject')->nullable();
            // When the seller first replied, used for the chat response time
            // component of BeeRank.
            $table->timestamp('first_reply_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'user_id']);
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
