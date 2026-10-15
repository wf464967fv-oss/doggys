<?php

namespace App\Models;

use App\Enums\ProviderSource;
use App\Enums\ProviderStatus;
use App\Enums\Region;
use App\Enums\VerificationLevel;
use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Sensitive columns (verification_level, status, plan, claimed_by_user_id, rating_avg, reviews_count)
 * are deliberately not fillable: set them explicitly with forceFill() from trusted code.
 */
#[Fillable([
    'name', 'slug', 'description', 'street', 'house_number', 'postal_code', 'city', 'region', 'lat', 'lng',
    'phone', 'email', 'website', 'opening_hours', 'is_mobile', 'service_radius_km', 'bce_number',
    'regulatory_number', 'source', 'source_ref', 'imported_at',
])]
class Provider extends Model
{
    /** @use HasFactory<ProviderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'region' => Region::class,
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'is_mobile' => 'boolean',
            'service_radius_km' => 'integer',
            'verification_level' => VerificationLevel::class,
            'status' => ProviderStatus::class,
            'source' => ProviderSource::class,
            'imported_at' => 'datetime',
            'rating_avg' => 'decimal:2',
            'reviews_count' => 'integer',
        ];
    }

    /**
     * @param  Builder<Provider>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', ProviderStatus::Active);
    }

    public function isVerified(): bool
    {
        return $this->verification_level !== VerificationLevel::None;
    }

    public function isRegulated(): bool
    {
        return $this->verification_level === VerificationLevel::Regulated;
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<Claim, $this>
     */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * @return HasMany<ProviderReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ProviderReport::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }
}
