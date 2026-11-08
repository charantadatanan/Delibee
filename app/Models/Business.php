<?php

namespace App\Models;

use App\Enums\BusinessStatus;
use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $logo_path
 * @property string $currency
 * @property BusinessStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['owner_id', 'name', 'slug', 'description', 'logo_path', 'currency', 'status'])]
class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BusinessStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return HasMany<Conversation, $this> */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /** @return HasMany<RankSnapshot, $this> */
    public function rankSnapshots(): HasMany
    {
        return $this->hasMany(RankSnapshot::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<Business>  $query
     * @return Builder<Business>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', BusinessStatus::Active);
    }

    /**
     * @param  Builder<Business>  $query
     * @return Builder<Business>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', BusinessStatus::Active);
    }

    public function isRising(): bool
    {
        return $this->created_at !== null
            && $this->created_at->gt(now()->subMonths(config('beerank.rising_bee_months')));
    }

    /**
     * Businesses eligible to be ranked: approved and still trading.
     */
    public function isRankable(): bool
    {
        return $this->status === BusinessStatus::Active;
    }
}
