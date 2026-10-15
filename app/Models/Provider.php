<?php

namespace App\Models;

use App\Enums\ProviderSource;
use App\Enums\ProviderStatus;
use App\Enums\Region;
use App\Enums\ReviewStatus;
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

    /**
     * A permanent deletion removes the claims and badge requests one by one first, so that their private
     * proof files are deleted too (the SQL cascade would skip the model events).
     */
    protected static function booted(): void
    {
        static::forceDeleting(function (Provider $provider): void {
            $provider->claims()->each(fn (Claim $claim) => $claim->delete());
            $provider->badgeRequests()->each(fn (BadgeRequest $badgeRequest) => $badgeRequest->delete());
        });
    }

    /**
     * Recomputes rating_avg and reviews_count from the published reviews.
     */
    public function refreshRating(): void
    {
        $count = $this->reviews()->where('status', ReviewStatus::Published)->count();
        $average = $this->reviews()->where('status', ReviewStatus::Published)->avg('rating');

        $values = [
            'rating_avg' => $count > 0 ? round((float) $average, 2) : null,
            'reviews_count' => $count,
        ];

        // Written straight to the row: a stale in-memory copy would make Eloquent skip "unchanged" columns.
        static::withTrashed()->whereKey($this->getKey())->toBase()->update($values);
        $this->forceFill($values)->syncOriginalAttributes(array_keys($values));
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
     * Belgian phone number in the familiar national format (0478 12 34 56, 02 123 45 67, 065 12 34 56).
     */
    public function phoneForDisplay(): ?string
    {
        if (! $this->phone || ! preg_match('/^\+32(\d{8,9})$/', $this->phone, $matches)) {
            return $this->phone;
        }

        $national = '0'.$matches[1];
        $groups = match (true) {
            strlen($national) === 10 => [4, 2, 2, 2],
            in_array($national[1], ['2', '3', '4', '9'], true) => [2, 3, 2, 2],
            default => [3, 2, 2, 2],
        };

        $parts = [];
        foreach ($groups as $length) {
            $parts[] = substr($national, 0, $length);
            $national = substr($national, $length);
        }

        return implode(' ', $parts);
    }

    /**
     * Opening hours (OpenStreetMap syntax) as readable lines in the current locale:
     * "Mo-Fr 09:00-18:00; Su off" => ["lun–ven 09:00–18:00", "dim fermé"].
     *
     * @return list<string>
     */
    public function openingHoursLines(): array
    {
        if (blank($this->opening_hours)) {
            return [];
        }

        $words = (array) __('directory.opening_tokens');

        return collect(explode(';', $this->opening_hours))
            ->map(fn (string $rule) => trim(str_replace('"', '', $rule)))
            ->filter()
            ->map(fn (string $rule) => preg_replace_callback(
                '/\b(Mo|Tu|We|Th|Fr|Sa|Su|PH|off|closed)\b|(?<=[a-zA-Z0-9])-(?=[a-zA-Z0-9])/',
                fn (array $match) => $match[0] === '-' ? '–' : ($words[$match[1]] ?? $match[0]),
                $rule,
            ))
            ->values()
            ->all();
    }

    /**
     * Opening hours rules valid for schema.org "openingHours" (simple OSM rules only).
     *
     * @return list<string>
     */
    public function schemaOpeningHours(): array
    {
        $day = '(Mo|Tu|We|Th|Fr|Sa|Su)';
        $time = '\d\d:\d\d-\d\d:\d\d';

        return collect(explode(';', (string) $this->opening_hours))
            ->map(fn (string $rule) => trim($rule))
            ->filter(fn (string $rule) => preg_match("/^{$day}([-,]{$day})* {$time}(,{$time})*$/", $rule) === 1)
            ->values()
            ->all();
    }

    /**
     * Most precise schema.org type for the listing's categories.
     */
    public function schemaType(): string
    {
        $slugs = $this->categories->pluck('slug');

        return match (true) {
            $slugs->intersect(['veterinaire', 'urgences-24h', 'dentisterie'])->isNotEmpty() => 'VeterinaryCare',
            $slugs->contains('refuge') => 'AnimalShelter',
            $slugs->contains('animalerie') => 'PetStore',
            $slugs->contains('parc-a-chiens') && $slugs->count() === 1 => 'Park',
            default => 'LocalBusiness',
        };
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
     * @return HasMany<BadgeRequest, $this>
     */
    public function badgeRequests(): HasMany
    {
        return $this->hasMany(BadgeRequest::class);
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
