<?php

namespace App\Models;

use Database\Factories\CommuneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Belgian municipality (OpenStreetMap admin_level=8, ODbL), with its official names and exonyms
 * (Anvers / Antwerpen / Antwerp, Ypres / Ieper…). City pages exist per commune.
 */
#[Fillable(['ref', 'names', 'slugs', 'lat', 'lng', 'population'])]
class Commune extends Model
{
    /** @use HasFactory<CommuneFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'names' => 'array',
            'slugs' => 'array',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'population' => 'integer',
        ];
    }

    public function label(?string $locale = null): string
    {
        return $this->names[$locale ?? app()->getLocale()] ?? $this->names['fr'] ?? reset($this->names);
    }

    public function slug(?string $locale = null): string
    {
        return $this->slugs[$locale ?? app()->getLocale()] ?? $this->slugs['fr'];
    }

    /**
     * URL slugs of the given names, transliterated per language (Lüttich => luettich).
     *
     * @param  array<string, string>  $names
     * @return array<string, string>
     */
    public static function slugsFor(array $names): array
    {
        return collect($names)->map(fn (string $name, string $locale) => Str::slug($name, '-', $locale))->all();
    }

    /**
     * @return HasMany<Provider, $this>
     */
    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    /**
     * @return HasMany<PostalCode, $this>
     */
    public function postalCodes(): HasMany
    {
        return $this->hasMany(PostalCode::class);
    }
}
