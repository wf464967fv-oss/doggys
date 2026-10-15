<?php

namespace App\Models;

use App\Enums\Region;
use Database\Factories\PostalCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Belgian postal code / locality (GeoNames, CC BY 4.0). A code can map to several localities.
 */
#[Fillable(['code', 'name', 'name_translations', 'lat', 'lng', 'province', 'region'])]
class PostalCode extends Model
{
    /** @use HasFactory<PostalCodeFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name_translations' => 'array',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'region' => Region::class,
        ];
    }

    /**
     * Locality name in the current locale when a translation exists (e.g. Liège / Luik / Lüttich).
     */
    public function label(?string $locale = null): string
    {
        return $this->name_translations[$locale ?? app()->getLocale()] ?? $this->name;
    }
}
