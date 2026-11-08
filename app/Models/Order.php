<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Carbon\CarbonInterface;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $number
 * @property int $business_id
 * @property int $user_id
 * @property OrderStatus $status
 * @property string $currency
 * @property int $subtotal_minor
 * @property int $shipping_minor
 * @property int $discount_minor
 * @property int $total_minor
 * @property array<string, mixed>|null $shipping_address
 * @property string|null $customer_note
 * @property Carbon|null $ordered_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'number',
    'business_id',
    'user_id',
    'status',
    'currency',
    'subtotal_minor',
    'shipping_minor',
    'discount_minor',
    'total_minor',
    'shipping_address',
    'customer_note',
    'ordered_at',
    'shipped_at',
    'delivered_at',
    'cancelled_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'shipping_address' => 'array',
            'subtotal_minor' => 'integer',
            'shipping_minor' => 'integer',
            'discount_minor' => 'integer',
            'total_minor' => 'integer',
            'ordered_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<OrderStatusEvent, $this> */
    public function statusEvents(): HasMany
    {
        return $this->hasMany(OrderStatusEvent::class)->orderBy('created_at');
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeForBusiness(Builder $query, Business|int $business): Builder
    {
        $businessId = $business instanceof Business ? $business->getKey() : $business;

        return $query->where('business_id', $businessId);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeBetween(Builder $query, CarbonInterface $from, CarbonInterface $to): Builder
    {
        return $query->whereBetween('ordered_at', [$from, $to]);
    }

    /**
     * A shipped order counts as on time when it was delivered no later than
     * the seller's promised window. The window is fixed for the MVP; a
     * per-seller field would replace it later.
     */
    public function isOnTime(): bool
    {
        if ($this->shipped_at === null || $this->delivered_at === null) {
            return false;
        }

        $days = (int) config('beerank.delivery_window_days');

        return $this->delivered_at->lessThanOrEqualTo(
            $this->shipped_at->copy()->addDays($days)
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === OrderStatus::Delivered;
    }
}
