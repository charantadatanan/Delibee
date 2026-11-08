<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $business_id
 * @property int|null $product_id
 * @property int|null $order_id
 * @property int|null $order_item_id
 * @property int $rating
 * @property string|null $title
 * @property string|null $body
 * @property ReviewStatus $status
 */
#[Fillable([
    'user_id',
    'business_id',
    'product_id',
    'order_id',
    'order_item_id',
    'rating',
    'title',
    'body',
    'status',
])]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'rating' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<OrderItem, $this> */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * @param  Builder<Review>  $query
     * @return Builder<Review>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::Published);
    }

    /**
     * Only reviews tied to a completed order item are verified purchases, and
     * only verified purchases count towards BeeRank.
     */
    public function isVerifiedPurchase(): bool
    {
        return $this->order_item_id !== null;
    }
}
