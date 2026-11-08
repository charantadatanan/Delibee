<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::OutForDelivery => 'Out for delivery',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    /**
     * The step in the delivery timeline that stamps the matching timestamp
     * on the order. Cancelled and refunded are tracked separately because
     * they are terminal states rather than delivery steps.
     */
    public function timestampColumn(): ?string
    {
        return match ($this) {
            self::Shipped, self::OutForDelivery => 'shipped_at',
            self::Delivered => 'delivered_at',
            self::Cancelled => 'cancelled_at',
            default => null,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Refunded], true);
    }

    public function isCancellable(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed, self::Processing], true);
    }

    /**
     * @return list<self>
     */
    public static function progression(): array
    {
        return [
            self::Pending,
            self::Confirmed,
            self::Processing,
            self::Shipped,
            self::OutForDelivery,
            self::Delivered,
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
