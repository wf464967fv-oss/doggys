<?php

namespace App\Services;

use App\Enums\VerificationLevel;
use App\Models\Category;
use App\Models\PostalCode;
use App\Models\Provider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Geographic provider search shared by the search page, the city pages and the JSON API.
 *
 * Everything heavy happens in portable SQL (SQLite and MySQL, no geo functions):
 * a bounding box on the indexed lat column, then an equirectangular distance
 * ((Δlat·111.045)² + (Δlng·111.045·cos lat₀)², exact to a few metres at Belgian scale) used to
 * filter the radius, sort and paginate. Only the displayed rows are hydrated; their haversine
 * distance is computed in PHP for display.
 */
class ProviderSearch
{
    /** @var list<int> */
    public const RADII = [5, 10, 25, 50];

    public const DEFAULT_RADIUS = 10;

    /** Results are counted (and paged) up to this number ("more than 200": 10 pages), so a wide search never scans the whole table. */
    public const COUNT_CAP = 200;

    /**
     * Furthest a travelling professional can come from (their own service radius applies, capped here).
     */
    public const MAX_SERVICE_RADIUS_KM = 100;

    private const KM_PER_DEGREE = 111.045;

    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Active providers around a point, closest first (column "distance_sq", km²).
     * Fixed-address pros must be within the radius; travelling pros (is_mobile) only when the point lies
     * within their own service radius (the search radius when they did not set one). $mobileOnly keeps only them.
     *
     * @return Builder<Provider>
     */
    public static function query(
        float $lat,
        float $lng,
        int $radiusKm,
        ?Category $category = null,
        bool $mobileOnly = false,
        bool $verifiedOnly = false,
        ?int $minRating = null,
        ?int $exceptId = null,
    ): Builder {
        $distance = self::distanceSql($lat, $lng);

        return Provider::query()
            ->active()
            ->select('providers.*')
            ->selectRaw("{$distance} as distance_sq")
            ->where(function (Builder $query) use ($lat, $lng, $radiusKm, $mobileOnly, $distance) {
                if (! $mobileOnly) {
                    $query->where(fn (Builder $query) => self::inBox($query->where('is_mobile', false), $lat, $lng, $radiusKm)
                        ->whereRaw("{$distance} <= ?", [$radiusKm ** 2]));
                }
                $query->orWhere(fn (Builder $query) => self::inBox($query->where('is_mobile', true), $lat, $lng, self::MAX_SERVICE_RADIUS_KM)
                    ->whereRaw("{$distance} <= COALESCE(service_radius_km, ?) * COALESCE(service_radius_km, ?)", [$radiusKm, $radiusKm]));
            })
            ->when($category, fn (Builder $query) => $query->whereIn('providers.id', fn ($pivot) => $pivot
                ->select('provider_id')->from('category_provider')->where('category_id', $category->getKey())))
            ->when($verifiedOnly, fn (Builder $query) => $query->where('verification_level', '!=', VerificationLevel::None))
            ->when($minRating, fn (Builder $query) => $query->where('rating_avg', '>=', $minRating))
            ->when($exceptId, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->orderByRaw($distance)
            ->orderBy('name')
            ->orderBy('providers.id');
    }

    /**
     * One page of a radius search, fast even where thousands of listings fall inside the radius:
     * the total is counted up to COUNT_CAP + 1 only, and the page is read from the smallest circle
     * (2, 5, 10, 25 km…) that already holds every row up to this page; those are the nearest ones anyway.
     *
     * @return LengthAwarePaginator<int, Provider>
     */
    public static function search(
        float $lat,
        float $lng,
        int $radiusKm,
        ?Category $category = null,
        bool $mobileOnly = false,
        bool $verifiedOnly = false,
        ?int $minRating = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $make = fn (int $radius) => self::query($lat, $lng, $radius, $category, $mobileOnly, $verifiedOnly, $minRating);
        $capped = fn ($query, int $limit) => DB::query()->fromSub($query->reorder()->select('providers.id')->limit($limit), 'capped')->count();

        $total = $capped($make($radiusKm), self::COUNT_CAP + 1);
        $needed = Paginator::resolveCurrentPage() * $perPage;
        $scan = $radiusKm;

        foreach ([2, 5, 10, 25] as $radius) {
            if ($radius >= $radiusKm || $needed > $total) {
                break;
            }
            $within = $make($radius)->whereRaw(self::distanceSql($lat, $lng).' <= ?', [$radius ** 2]);
            if ($capped($within, $needed) >= $needed) {
                $scan = $radius;
                break;
            }
        }

        return self::paginate($make($scan), $lat, $lng, $perPage, $total);
    }

    /**
     * One page of a search: only those rows are hydrated, with their categories and distance.
     * Pages beyond the last one are a 404, like any unknown URL.
     *
     * @param  Builder<Provider>  $query
     * @return LengthAwarePaginator<int, Provider>
     */
    public static function paginate(Builder $query, float $lat, float $lng, int $perPage = 20, ?int $total = null): LengthAwarePaginator
    {
        // Sort narrow rows (id only), then load the full rows of this page: sorting thousands of complete
        // rows is what makes a wide search slow. A known $total saves the COUNT query.
        $ids = (clone $query)->select('providers.id');
        $page = $total === null
            ? $ids->paginate($perPage)->withQueryString()
            : new Paginator(
                $ids->forPage(Paginator::resolveCurrentPage(), $perPage)->get(),
                $total,
                $perPage,
                Paginator::resolveCurrentPage(),
                ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()],
            );
        abort_if($page->currentPage() > max(1, $page->lastPage()), 404);

        $ids = $page->getCollection()->modelKeys();
        $providers = Provider::query()->whereKey($ids)->with('categories')->get()->sortBy(fn (Provider $provider) => array_search($provider->id, $ids, true))->values();
        self::setDistances($providers, $lat, $lng);

        return $page->setCollection($providers);
    }

    /**
     * Sets "distance_km" (haversine, one decimal) on the given providers.
     *
     * @param  iterable<Provider>  $providers
     */
    public static function setDistances(iterable $providers, float $lat, float $lng): void
    {
        foreach ($providers as $provider) {
            $provider->setAttribute('distance_km', round(self::distanceKm($lat, $lng, (float) $provider->lat, (float) $provider->lng), 1));
        }
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
     * Turns "4000 Liège", "4000", "Liège", "Luik" or "Anvers" into coordinates, or null when nothing matches.
     *
     * @return array{lat: float, lng: float, label: string}|null
     */
    public static function locate(string $input): ?array
    {
        $input = trim($input);

        if (preg_match('/^(\d{4})\b\s*(.*)$/u', $input, $matches)) {
            $rows = PostalCode::query()->where('code', $matches[1])->orderBy('id')->get();
            $wanted = Str::slug($matches[2]);
            $row = $rows->first(fn (PostalCode $row) => $wanted !== '' && Str::slug($row->name) === $wanted) ?? $rows->first();

            return $row ? ['lat' => (float) $row->lat, 'lng' => (float) $row->lng, 'label' => $row->code.' '.$row->label()] : null;
        }

        $place = PostalCode::searchPlaces(Str::slug($input), 1)[0] ?? null;

        return $place ? ['lat' => $place['lat'], 'lng' => $place['lng'], 'label' => $place['names'][app()->getLocale()]] : null;
    }

    /**
     * Squared equirectangular distance in km² to the point, as a portable SQL expression.
     * The coordinates are floats formatted by PHP (never user strings), so inlining them is safe.
     */
    private static function distanceSql(float $lat, float $lng): string
    {
        $kx = self::KM_PER_DEGREE * cos(deg2rad($lat));
        $dy = sprintf('((lat - %.7F) * %.4F)', $lat, self::KM_PER_DEGREE);
        $dx = sprintf('((lng - %.7F) * %.6F)', $lng, $kx);

        return "({$dy} * {$dy} + {$dx} * {$dx})";
    }

    /**
     * Restricts the query to a square of ±$radiusKm around the point (uses the lat index).
     *
     * @param  Builder<Provider>  $query
     * @return Builder<Provider>
     */
    private static function inBox(Builder $query, float $lat, float $lng, int $radiusKm): Builder
    {
        $dLat = $radiusKm / self::KM_PER_DEGREE;
        $dLng = $radiusKm / (self::KM_PER_DEGREE * max(0.01, cos(deg2rad($lat))));

        return $query->whereBetween('lat', [$lat - $dLat, $lat + $dLat])->whereBetween('lng', [$lng - $dLng, $lng + $dLng]);
    }
}
