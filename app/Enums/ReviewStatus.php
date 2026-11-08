<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting moderation',
            self::Published => 'Published',
            self::Hidden => 'Hidden',
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
