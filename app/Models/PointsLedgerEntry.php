<?php

namespace App\Models;

use Database\Factories\PointsLedgerEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $delta
 * @property string $reason
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property Carbon|null $created_at
 */
#[Fillable(['user_id', 'delta', 'reason', 'reference_type', 'reference_id'])]
class PointsLedgerEntry extends Model
{
    /** @use HasFactory<PointsLedgerEntryFactory> */
    use HasFactory;

    protected $table = 'points_ledger';

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delta' => 'integer',
            'reference_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
