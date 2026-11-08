<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The trigram index below needs pg_trgm, so the extension is enabled
        // here rather than assumed to be present on the server.
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            // Money is stored in minor units (cents/centavos) as an integer.
            $table->unsignedBigInteger('price_minor');
            $table->string('currency', 3)->default('PHP');
            $table->unsignedInteger('stock')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['business_id', 'slug']);
            $table->index(['status', 'category_id']);
            $table->index('created_at');
        });

        // Full text search lives in Postgres so the marketplace needs no
        // external search service.
        DB::statement(<<<'SQL'
            ALTER TABLE products
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                setweight(to_tsvector('simple', coalesce(name, '')), 'A') ||
                setweight(to_tsvector('simple', coalesce(description, '')), 'B')
            ) STORED
        SQL);

        DB::statement('CREATE INDEX products_search_vector_index ON products USING GIN (search_vector)');
        DB::statement('CREATE INDEX products_name_trgm_index ON products USING GIN (name gin_trgm_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
