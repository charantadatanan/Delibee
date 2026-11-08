<?php

namespace App\Models;

use Database\Factories\RankSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $business_id
 * @property string $period_type
 * @property Carbon $period_start
 * @property float $score
 * @property int $rank
 * @property array<string, mixed> $metrics
 * @property Carbon|null $created_at
 */
#[Fillable(['business_id', 'period_type', 'period_start', 'score', 'rank', 'metrics'])]
class RankSnapshot extends Model
{
    /** @use HasFactory<RankSnapshotFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'score' => 'decimal:4',
            'rank' => 'integer',
            'metrics' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
