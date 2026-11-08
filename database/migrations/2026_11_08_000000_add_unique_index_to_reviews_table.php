<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One review per purchased order item per customer. The application
        // already writes this way; the index enforces it under concurrency.
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'order_item_id']);
        });
    }
};
