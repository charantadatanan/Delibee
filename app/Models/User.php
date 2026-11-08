<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** @return HasOne<Profile, $this> */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /** @return HasMany<Business, $this> */
    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class, 'owner_id');
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

    /** @return HasMany<CartItem, $this> */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** @return HasMany<PointsLedgerEntry, $this> */
    public function pointsLedger(): HasMany
    {
        return $this->hasMany(PointsLedgerEntry::class);
    }

    /** @return HasMany<Conversation, $this> */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * The Honey Points balance is the sum of the ledger, never a stored
     * number, so every point is auditable and reversible.
     */
    public function pointsBalance(): int
    {
        return (int) $this->pointsLedger()->sum('delta');
    }

    public function isSeller(): bool
    {
        return $this->hasRole('seller');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * The business this user owns for selling, if any.
     */
    public function sellerBusiness(): ?Business
    {
        return $this->businesses()->orderBy('id')->first();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
