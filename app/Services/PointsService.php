<?php

namespace App\Services;

use App\Enums\PointsReason;
use App\Models\PointsLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Honey Points are an append-only ledger, never a number on the profile.
 * The balance is the sum of the deltas, which keeps every point auditable
 * and lets a refund be reversed by writing another row.
 */
class PointsService
{
    public function award(
        User $user,
        int $points,
        PointsReason $reason,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): PointsLedgerEntry {
        return $this->record($user, abs($points), $reason, $referenceType, $referenceId);
    }

    public function redeem(
        User $user,
        int $points,
        PointsReason $reason = PointsReason::Redemption,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): PointsLedgerEntry {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Redemption amount must be positive.');
        }

        if ($this->balance($user) < $points) {
            throw new ModelNotFoundException('Not enough Honey Points to redeem.');
        }

        return $this->record($user, -$points, $reason, $referenceType, $referenceId);
    }

    /**
     * Reverse points that were already awarded, e.g. when an order is
     * cancelled or refunded.
     */
    public function reverse(
        User $user,
        int $points,
        PointsReason $reason,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): PointsLedgerEntry {
        return $this->record($user, -abs($points), $reason, $referenceType, $referenceId);
    }

    public function balance(User $user): int
    {
        return (int) $user->pointsLedger()->sum('delta');
    }

    private function record(
        User $user,
        int $delta,
        PointsReason $reason,
        ?string $referenceType,
        ?int $referenceId,
    ): PointsLedgerEntry {
        return PointsLedgerEntry::create([
            'user_id' => $user->id,
            'delta' => $delta,
            'reason' => $reason->value,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }
}
