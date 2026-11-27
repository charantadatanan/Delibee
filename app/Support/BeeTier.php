<?php

namespace App\Support;

/**
 * The four Honey Rewards tiers shown in the Figma profile and rewards
 * screens. Kept in one place so the header chip, dashboard and rewards page
 * never disagree.
 */
final class BeeTier
{
    /**
     * @return array{name: string, next: string|null, next_at: int|null, progress: int, badge: string}
     */
    public static function for(int $points): array
    {
        $points = max(0, $points);

        $tiers = [
            ['name' => 'Larva', 'at' => 0, 'badge' => 'bg-[#FFE7A3]'],
            ['name' => 'Forager', 'at' => 300, 'badge' => 'bg-[#FFC233]'],
            ['name' => 'Courtier', 'at' => 1000, 'badge' => 'bg-[#F3B6A0]'],
            ['name' => 'Queen', 'at' => 2500, 'badge' => 'bg-[#CDB6F2]'],
        ];

        $index = 0;

        foreach ($tiers as $position => $tier) {
            if ($points >= $tier['at']) {
                $index = $position;
            }
        }

        $current = $tiers[$index];
        $next = $tiers[$index + 1] ?? null;

        $progress = 100;

        if ($next !== null) {
            $span = $next['at'] - $current['at'];
            $progress = $span > 0
                ? (int) min(100, max(0, round((($points - $current['at']) / $span) * 100)))
                : 100;
        }

        return [
            'name' => $current['name'],
            'next' => $next['name'] ?? null,
            'next_at' => $next['at'] ?? null,
            'progress' => $progress,
            'badge' => $current['badge'],
        ];
    }
}
