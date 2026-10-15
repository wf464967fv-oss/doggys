<?php

namespace App\Models;

use App\Enums\Region;
use App\Services\ProviderSearch;
use Database\Factories\PostalCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Belgian postal code / locality (GeoNames, CC BY 4.0). A code can map to several localities.
 */
#[Fillable(['code', 'name', 'name_translations', 'lat', 'lng', 'province', 'region'])]
class PostalCode extends Model
{
    /** @use HasFactory<PostalCodeFactory> */
    use HasFactory;

    public const CITY_INDEX_CACHE_KEY = 'directory.city-index.v2';

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

    /**
     * Localities ("cities") built from the postal codes, used by city pages, search and the import.
     * Localities sharing a name are merged (Liège 4000, 4020…) unless they lie more than 20 km apart:
     * homonyms (Aalst 9300 / Aalst 3800) then get the postal code appended to their key.
     * The main locality of a code is the translated big city, else the name covering the most codes.
     * Cached for a day: clear CITY_INDEX_CACHE_KEY after reseeding postal codes.
     *
     * @return array{cities: array<string, array{key: string, names: array<string, string>, slugs: array<string, string>, lat: float, lng: float, codes: list<string>}>, by_code: array<string, string>, by_locality: array<string, string>, by_slug: array<string, array<string, string>>}
     */
    public static function cityIndex(): array
    {
        return once(fn () => Cache::remember(self::CITY_INDEX_CACHE_KEY, now()->addDay(), function () {
            $rows = static::query()->orderBy('id')->get(['id', 'code', 'name', 'name_translations', 'lat', 'lng']);
            $codesPerName = $rows->groupBy('name')->map(fn ($group) => $group->pluck('code')->unique()->count());
            $firstOfCode = $rows->groupBy('code')->map(fn ($group) => $group->first()->id)->flip();
            $rows = $rows->sortBy([
                fn (self $a, self $b) => (bool) $b->name_translations <=> (bool) $a->name_translations,
                fn (self $a, self $b) => $codesPerName[$b->name] <=> $codesPerName[$a->name],
                fn (self $a, self $b) => isset($firstOfCode[$b->id]) <=> isset($firstOfCode[$a->id]),
                fn (self $a, self $b) => $a->id <=> $b->id,
            ]);

            $cities = [];
            $byCode = [];
            $fallbackByCode = [];
            $byLocality = [];

            foreach ($rows as $row) {
                $slug = Str::slug($row->name);
                $key = $slug;

                if (isset($cities[$key]) && ProviderSearch::distanceKm($cities[$key]['anchor'][0], $cities[$key]['anchor'][1], (float) $row->lat, (float) $row->lng) > 20) {
                    $key = $slug.'-'.$row->code;
                }

                if (! isset($cities[$key])) {
                    $names = [];
                    foreach (config('app.locales') as $locale) {
                        // A homonym village keeps its local name (Aalst near Sint-Truiden is not "Alost").
                        $names[$locale] = $key === $slug ? $row->label($locale) : $row->name;
                    }

                    $cities[$key] = [
                        'key' => $key,
                        'names' => $names,
                        'slugs' => array_map(fn (string $name) => $key === $slug ? Str::slug($name) : Str::slug($name).'-'.$row->code, $names),
                        'anchor' => [(float) $row->lat, (float) $row->lng],
                        'points' => [],
                        'codes' => [],
                    ];
                }

                $cities[$key]['points'][$row->code] = [(float) $row->lat, (float) $row->lng];
                if ($key === $slug) {
                    $byCode[$row->code] ??= $key;
                }
                $fallbackByCode[$row->code] ??= $key;
                $byLocality[$row->code.'|'.$slug] = $key;
            }

            $byCode += $fallbackByCode;

            $bySlug = array_fill_keys(config('app.locales'), []);

            foreach ($cities as $key => $city) {
                $points = $city['points'];
                unset($city['anchor'], $city['points']);

                $city['lat'] = round(array_sum(array_column($points, 0)) / count($points), 5);
                $city['lng'] = round(array_sum(array_column($points, 1)) / count($points), 5);
                $city['codes'] = array_map('strval', array_keys($points));
                $cities[$key] = $city;

                foreach ($city['slugs'] as $locale => $citySlug) {
                    $bySlug[$locale][$citySlug] ??= $key;
                }
            }

            return ['cities' => $cities, 'by_code' => $byCode, 'by_locality' => $byLocality, 'by_slug' => $bySlug];
        }));
    }

    /**
     * City of an address: the locality of that name within the postal code when it exists
     * (Aalst in 3800 is not Aalst in 9300), otherwise the main locality of the code.
     *
     * @return array{key: string, names: array<string, string>, slugs: array<string, string>, lat: float, lng: float, codes: list<string>}|null
     */
    public static function cityFor(?string $code, ?string $name = null): ?array
    {
        $index = static::cityIndex();
        $key = $index['by_locality'][$code.'|'.Str::slug((string) $name)] ?? $index['by_code'][(string) $code] ?? null;

        return $key ? $index['cities'][$key] : null;
    }

    /**
     * City from its URL slug in the given locale (luik in Dutch, luttich in German…).
     *
     * @return array{key: string, names: array<string, string>, slugs: array<string, string>, lat: float, lng: float, codes: list<string>}|null
     */
    public static function cityForSlug(string $slug, ?string $locale = null): ?array
    {
        $index = static::cityIndex();
        $key = $index['by_slug'][$locale ?? app()->getLocale()][$slug] ?? null;

        return $key ? $index['cities'][$key] : null;
    }
}
