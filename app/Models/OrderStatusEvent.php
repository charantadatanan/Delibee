<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderStatusEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property OrderStatus $status
 * @property string|null $note
 * @property int|null $created_by
 * @property Carbon|null $created_at
 */
#[Fillable(['order_id', 'status', 'note', 'created_by'])]
class OrderStatusEvent extends Model
{
    /** @use HasFactory<OrderStatusEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
