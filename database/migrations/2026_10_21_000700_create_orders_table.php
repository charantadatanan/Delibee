<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            // One order per seller: a cart spanning many businesses is split
            // into one order per business at checkout.
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('pending');
            $table->string('currency', 3)->default('PHP');
            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('shipping_minor')->default(0);
            $table->unsignedBigInteger('discount_minor')->default(0);
            $table->unsignedBigInteger('total_minor');
            $table->jsonb('shipping_address')->nullable();
            $table->text('customer_note')->nullable();
            // Timestamps recorded from day one: rankings are computed from
            // them and cannot be reconstructed later.
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('ordered_at');
            $table->index('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
