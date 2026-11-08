<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $quantity
 */
#[Fillable(['user_id', 'product_id', 'quantity'])]
class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The price is read live from the product rather than snapshotted: the
     * cart is not a commitment, and the snapshot belongs on the order.
     */
    public function subtotalMinor(): int
    {
        $price = $this->product?->price_minor;

        return $this->quantity * ($price ?? 0);
    }
}
