<?php

namespace App\Enums;

enum PointsReason: string
{
    case WelcomeBonus = 'welcome_bonus';
    case Purchase = 'purchase';
    case ReviewReward = 'review_reward';
    case Redemption = 'redemption';
    case Refund = 'refund';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::WelcomeBonus => 'Welcome bonus',
            self::Purchase => 'Purchase reward',
            self::ReviewReward => 'Review reward',
            self::Redemption => 'Reward redeemed',
            self::Refund => 'Refund reversal',
            self::Adjustment => 'Manual adjustment',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
