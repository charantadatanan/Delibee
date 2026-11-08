<?php

namespace App\Models;

use App\Enums\BusinessStatus;
use App\Enums\ProductStatus;
use App\Support\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $business_id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $price_minor
 * @property string $currency
 * @property int $stock
 * @property ProductStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['business_id', 'category_id', 'name', 'slug', 'description', 'price_minor', 'currency', 'stock', 'status'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'price_minor' => 'integer',
            'stock' => 'integer',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<ProductImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    /** @return HasMany<CartItem, $this> */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Active listings from active businesses only: a product belonging to a
     * pending or suspended seller must never reach the public storefront.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatus::Active)
            ->whereHas('business', fn (Builder $query): Builder => $query->where('status', BusinessStatus::Active));
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Full text search over the generated tsvector column, with a trigram
     * fallback so partial and misspelled words still find something.
     */
    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term): void {
            $query->whereRaw("search_vector @@ websearch_to_tsquery('simple', ?)", [$term])
                ->orWhereRaw('name ILIKE ?', ['%'.$term.'%'])
                ->orWhereRaw('description ILIKE ?', ['%'.$term.'%']);
        });
    }

    /**
     * Rank matches by relevance of the full text vector, then by recency.
     */
    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeOrderByRelevance(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query->orderByDesc('created_at');
        }

        return $query
            ->orderByRaw("ts_rank(search_vector, websearch_to_tsquery('simple', ?)) DESC", [$term])
            ->orderByDesc('created_at');
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function formattedPrice(): string
    {
        return Money::format($this->price_minor, $this->currency);
    }
}
