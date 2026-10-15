<?php

namespace App\Models;

use App\Enums\Region;
use Database\Factories\PostalCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Belgian postal code / locality (GeoNames, CC BY 4.0). A code can map to several localities,
 * each belonging to one municipality (Commune).
 */
#[Fillable(['code', 'name', 'name_translations', 'lat', 'lng', 'province', 'region', 'commune_id'])]
class PostalCode extends Model
{
    /** @use HasFactory<PostalCodeFactory> */
    use HasFactory;

    public const PLACE_INDEX_CACHE_KEY = 'directory.place-index.v3';

    /**
     * Lowercase words inside hyphenated place names (GeoNames capitalises every segment).
     */
    private const SMALL_WORDS = ['sur', 'sous', 'lez', 'lès', 'la', 'le', 'les', 'de', 'du', 'des', 'aux', 'en', 'et', 'devant', 'aan', 'op', 'bij', 'ter', 'den', 'in', 'het', 'over'];

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
     * @return BelongsTo<Commune, $this>
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Official casing of a GeoNames place name: "Vaux-Sur-Sûre" => "Vaux-sur-Sûre",
     * "Braine-L'Alleud" => "Braine-l'Alleud", "Beveren-Aan-Den-Ijzer" => "Beveren-aan-den-IJzer".
     * Same rules as placeCase() in scripts/data/communes.mjs.
     */
    public static function placeCase(string $name): string
    {
        $parts = explode('-', $name);

        foreach ($parts as $index => $part) {
            if ($index === 0) {
                continue;
            }
            $parts[$index] = in_array(mb_strtolower($part), self::SMALL_WORDS, true)
                ? mb_strtolower($part)
                : preg_replace_callback("/^([LD])'/", fn (array $match) => mb_strtolower($match[1])."'", $part);
        }

        return preg_replace(["/^'S /", '/\bIj/u'], ["'s ", 'IJ'], implode('-', $parts));
    }

    /**
     * Places a visitor may type in the search box: every municipality (in all its languages) and every
     * postal locality not already named like its municipality. One entry per name, with the smallest
     * postal code ("Liège" => 4000, not 4020). Cached for a day; forgetPlaceIndex() after reseeding.
     *
     * @return list<array{names: array<string, string>, slugs: list<string>, code: string, lat: float, lng: float, commune_id: int|null, size: int}>
     */
    public static function placeIndex(): array
    {
        return once(fn () => Cache::remember(self::PLACE_INDEX_CACHE_KEY, now()->addDay(), function () {
            $locales = config('app.locales');
            $localities = static::query()->orderBy('code')->orderBy('id')->get(['code', 'name', 'name_translations', 'lat', 'lng', 'commune_id']);
            $firstCode = $localities->groupBy('commune_id')->map(fn ($rows) => $rows->first()->code);
            $sizes = $localities->countBy('commune_id');
            $places = [];
            $taken = [];

            foreach (Commune::query()->get() as $commune) {
                $slugs = array_values(array_unique([...array_values($commune->slugs), ...array_map(Str::slug(...), $commune->names)]));
                $places[] = [
                    'names' => $commune->names,
                    'slugs' => $slugs,
                    'code' => (string) ($firstCode[$commune->id] ?? ''),
                    'lat' => (float) $commune->lat,
                    'lng' => (float) $commune->lng,
                    'commune_id' => $commune->id,
                    'size' => ($sizes[$commune->id] ?? 0) + 1,
                ];
                $taken += array_fill_keys($slugs, true);
            }

            foreach ($localities->groupBy(fn (self $row) => Str::slug($row->name)) as $slug => $rows) {
                if (isset($taken[$slug])) {
                    continue;
                }
                $row = $rows->first();
                $places[] = [
                    'names' => array_combine($locales, array_map(fn (string $locale) => $row->label($locale), $locales)),
                    'slugs' => [(string) $slug],
                    'code' => $row->code,
                    'lat' => (float) $row->lat,
                    'lng' => (float) $row->lng,
                    'commune_id' => $row->commune_id,
                    'size' => 0,
                ];
            }

            return $places;
        }));
    }

    public static function forgetPlaceIndex(): void
    {
        Cache::forget(self::PLACE_INDEX_CACHE_KEY);
    }

    /**
     * Places whose name (in any language) starts with the given slug: exact matches first, then municipalities,
     * then the biggest ones.
     *
     * @return list<array{names: array<string, string>, slugs: list<string>, code: string, lat: float, lng: float, commune_id: int|null, size: int}>
     */
    public static function searchPlaces(string $slugPrefix, int $limit): array
    {
        if ($slugPrefix === '') {
            return [];
        }

        return collect(static::placeIndex())
            ->map(fn (array $place) => $place + ['rank' => in_array($slugPrefix, $place['slugs'], true) ? 2
                : (collect($place['slugs'])->contains(fn (string $slug) => str_starts_with($slug, $slugPrefix)) ? 1 : 0)])
            ->where('rank', '>', 0)
            ->sortBy([['rank', 'desc'], ['size', 'desc'], ['code', 'asc']])
            ->take($limit)
            ->map(fn (array $place) => collect($place)->except('rank')->all())
            ->values()
            ->all();
    }
}
