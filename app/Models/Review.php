<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['rating', 'body'])]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    /** Days before the same account may review the same professional again. */
    public const COOLDOWN_DAYS = 30;

    /** Reviews an account may publish within 24 hours. */
    public const DAILY_LIMIT = 5;

    /**
     * The professional's rating and review count follow every change (creation, edit, hiding, deletion).
     */
    protected static function booted(): void
    {
        static::saved(fn (Review $review) => $review->provider?->refreshRating());
        static::deleted(fn (Review $review) => $review->provider?->refreshRating());
    }

    /**
     * Why the user may not review this professional right now (key of reviews.blocked.*), or null when allowed.
     */
    public static function blockReason(User $user, Provider $provider): ?string
    {
        return match (true) {
            ! $user->hasVerifiedEmail() => 'unverified',
            $provider->claimed_by_user_id === $user->id => 'own_listing',
            $user->reviews()->where('provider_id', $provider->id)
                ->where('created_at', '>=', now()->subDays(self::COOLDOWN_DAYS))->exists() => 'cooldown',
            $user->reviews()->where('created_at', '>=', now()->subDay())->count() >= self::DAILY_LIMIT => 'daily_limit',
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'status' => ReviewStatus::class,
            'provider_replied_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * @return HasMany<ReviewReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }
}
