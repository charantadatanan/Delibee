<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL does not enforce Laravel's "unsigned" column modifiers, and a few
 * business invariants were application-only. These CHECK constraints make the
 * database defend itself against negative money, negative stock and invalid
 * quantities/ratings.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products ADD CONSTRAINT products_price_non_negative CHECK (price_minor >= 0)');
        DB::statement('ALTER TABLE products ADD CONSTRAINT products_stock_non_negative CHECK (stock >= 0)');

        DB::statement('ALTER TABLE reviews ADD CONSTRAINT reviews_rating_range CHECK (rating BETWEEN 1 AND 5)');

        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_quantity_positive CHECK (quantity > 0)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_amounts_non_negative CHECK (unit_price_minor >= 0 AND subtotal_minor >= 0)');

        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT cart_items_quantity_positive CHECK (quantity > 0)');

        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_amounts_non_negative CHECK (subtotal_minor >= 0 AND shipping_minor >= 0 AND discount_minor >= 0 AND total_minor >= 0)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_price_non_negative');
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_stock_non_negative');

        DB::statement('ALTER TABLE reviews DROP CONSTRAINT IF EXISTS reviews_rating_range');

        DB::statement('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_quantity_positive');
        DB::statement('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_amounts_non_negative');

        DB::statement('ALTER TABLE cart_items DROP CONSTRAINT IF EXISTS cart_items_quantity_positive');

        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_amounts_non_negative');
    }
};
