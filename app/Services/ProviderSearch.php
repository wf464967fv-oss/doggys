<?php

namespace App\Services;

use App\Enums\VerificationLevel;
use App\Models\Category;
use App\Models\PostalCode;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;

/**
 * Geographic provider search shared by the search page, the city pages and the JSON API.
 * Portable SQL (SQLite/MySQL): a bounding box narrows rows in the database, the exact
 * haversine distance is computed in PHP, then results are sorted by distance.
 */
class ProviderSearch
{
    /** @var list<int> */
    public const RADII = [5, 10, 25, 50];

    public const DEFAULT_RADIUS = 10;

    /**
     * Furthest a travelling professional is looked up from (their own service radius applies next).
     * ponytail: fixed cap keeps the bounding box small; raise it if pros declare wider service areas.
     */
    public const MAX_SERVICE_RADIUS_KM = 100;

    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Active providers around a point, closest first, each with a "distance_km" attribute.
     * Travelling pros (is_mobile) only appear when the point lies within their service radius
     * (the search radius when they did not set one). $mobileOnly keeps only them.
     *
     * @return Collection<int, Provider>
     */
    public static function nearby(
        float $lat,
        float $lng,
        int $radiusKm,
        ?Category $category = null,
        bool $mobileOnly = false,
        bool $verifiedOnly = false,
        ?int $minRating = null,
        ?int $exceptId = null,
    ): Collection {
        $providers = Provider::query()
            ->active()
            ->with('categories')
            ->where(function (Builder $query) use ($lat, $lng, $radiusKm, $mobileOnly) {
                if (! $mobileOnly) {
                    $query->where(fn (Builder $query) => self::inBox($query, $lat, $lng, $radiusKm));
                }
                $query->orWhere(fn (Builder $query) => self::inBox($query->where('is_mobile', true), $lat, $lng, self::MAX_SERVICE_RADIUS_KM));
            })
            ->when($category, fn (Builder $query) => $query->whereHas('categories', fn (Builder $query) => $query->whereKey($category->getKey())))
            ->when($verifiedOnly, fn (Builder $query) => $query->where('verification_level', '!=', VerificationLevel::None))
            ->when($minRating, fn (Builder $query) => $query->where('rating_avg', '>=', $minRating))
            ->when($exceptId, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->get();

        return $providers
            ->each(fn (Provider $provider) => $provider->setAttribute(
                'distance_km',
                round(self::distanceKm($lat, $lng, (float) $provider->lat, (float) $provider->lng), 1),
            ))
            ->filter(fn (Provider $provider) => $provider->is_mobile
                ? $provider->distance_km <= ($provider->service_radius_km ?? $radiusKm)
                : $provider->distance_km <= $radiusKm)
            ->sortBy([['distance_km', 'asc'], ['name', 'asc']])
            ->values();
    }

    /**
     * Great-circle distance in kilometres (haversine).
     */
    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $h = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * self::EARTH_RADIUS_KM * asin(min(1, sqrt($h)));
    }

    /**
     * Turns "4000 Liège", "4000", "Liège" or "Luik" into coordinates, or null when nothing matches.
     *
     * @return array{lat: float, lng: float, label: string}|null
     */
    public static function locate(string $input): ?array
    {
        $input = trim($input);

        if (preg_match('/^(\d{4})\b\s*(.*)$/u', $input, $matches)) {
            $rows = PostalCode::query()->where('code', $matches[1])->orderBy('id')->get();
            $wanted = Str::slug($matches[2]);
            $row = $rows->first(fn (PostalCode $row) => $wanted !== '' && Str::slug($row->name) === $wanted)
                ?? $rows->first(fn (PostalCode $row) => PostalCode::cityFor($row->code)['key'] === Str::slug($row->name))
                ?? $rows->first();

            return $row ? ['lat' => (float) $row->lat, 'lng' => (float) $row->lng, 'label' => $row->code.' '.$row->label()] : null;
        }

        $slug = Str::slug($input);

        if ($slug === '') {
            return null;
        }

        $city = self::cities($slug, 1)[0] ?? null;

        return $city ? ['lat' => $city['lat'], 'lng' => $city['lng'], 'label' => $city['names'][app()->getLocale()]] : null;
    }

    /**
     * Cities whose name (in any language) starts with the given slug: exact matches first, then bigger cities.
     *
     * @return list<array{key: string, names: array<string, string>, slugs: array<string, string>, lat: float, lng: float, codes: list<string>}>
     */
    public static function cities(string $slugPrefix, int $limit): array
    {
        return collect(PostalCode::cityIndex()['cities'])
            ->map(function (array $city) use ($slugPrefix) {
                $slugs = array_unique([...array_values($city['slugs']), $city['key']]);
                $city['rank'] = in_array($slugPrefix, $slugs, true) ? 2
                    : (collect($slugs)->contains(fn (string $slug) => str_starts_with($slug, $slugPrefix)) ? 1 : 0);
                $city['size'] = count($city['codes']);

                return $city;
            })
            ->where('rank', '>', 0)
            ->sortBy([['rank', 'desc'], ['size', 'desc'], ['key', 'asc']])
            ->take($limit)
            ->map(fn (array $city) => collect($city)->except(['rank', 'size'])->all())
            ->values()
            ->all();
    }

    /**
     * Paginates an in-memory result list, keeping the current query string in the links.
     *
     * @param  Collection<int, Provider>  $providers
     * @return LengthAwarePaginator<int, Provider>
     */
    public static function paginate(Collection $providers, int $perPage = 20): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $providers->forPage($page, $perPage)->values(),
            $providers->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    /**
     * Restricts the query to a square of ±$radiusKm around the point (indexed lat/lng columns).
     *
     * @param  Builder<Provider>  $query
     * @return Builder<Provider>
     */
    private static function inBox(Builder $query, float $lat, float $lng, int $radiusKm): Builder
    {
        $dLat = $radiusKm / 111.045;
        $dLng = $radiusKm / (111.045 * max(0.01, cos(deg2rad($lat))));

        return $query->whereBetween('lat', [$lat - $dLat, $lat + $dLat])->whereBetween('lng', [$lng - $dLng, $lng + $dLng]);
    }
}
