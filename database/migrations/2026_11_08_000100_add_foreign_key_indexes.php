<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PostgreSQL indexes the referenced side of a foreign key automatically, but
 * not the referencing columns. These are the lookup paths the application
 * actually uses (relations, order timelines, chat inbox, category filters).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->index('owner_id');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index('sender_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('order_status_events', function (Blueprint $table) {
            $table->index('created_by');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropIndex(['owner_id']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['sender_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        Schema::table('order_status_events', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['order_item_id']);
        });
    }
};
