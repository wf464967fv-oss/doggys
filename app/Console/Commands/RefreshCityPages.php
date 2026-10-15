<?php

namespace App\Console\Commands;

use App\Enums\ProviderStatus;
use App\Http\Controllers\CategoryController;
use App\Models\Category;
use App\Models\Commune;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Precomputes the city pages (category x commune) and which ones deserve to be indexed:
 * at least MIN_INDEXABLE pros within the page radius, and a first page that does not repeat, for
 * 70 % or more, the first page of a bigger neighbouring commune (sections and suburbs of a city
 * otherwise produce near-identical pages). Links, sitemap and robots meta read this table.
 * Run after every import (doggys:import-providers calls it).
 */
#[Signature('doggys:refresh-city-pages')]
#[Description('Recompute city pages (category x commune) and their indexability')]
class RefreshCityPages extends Command
{
    public const MIN_INDEXABLE = 3;

    public const MAX_OVERLAP = 0.7;

    private const PAGE_SIZE = 20;

    private const KM_PER_DEGREE = 111.045;

    public function handle(): int
    {
        $communes = Commune::query()->get(['id', 'lat', 'lng', 'population'])
            ->map(fn (Commune $commune) => ['id' => $commune->id, 'lat' => (float) $commune->lat, 'lng' => (float) $commune->lng, 'population' => $commune->population])
            ->keyBy('id')
            ->all();
        $radius = CategoryController::CITY_RADIUS_KM;
        $neighbours = $this->neighbours($communes, $radius);
        $total = 0;

        foreach (Category::query()->pluck('id') as $categoryId) {
            $fixed = [];
            $mobile = [];
            $located = [];

            foreach (DB::table('providers')
                ->join('category_provider', 'category_provider.provider_id', '=', 'providers.id')
                ->where('category_provider.category_id', $categoryId)
                ->where('providers.status', ProviderStatus::Active->value)
                ->whereNull('providers.deleted_at')
                ->whereNotNull('providers.lat')
                ->select(['providers.id', 'providers.lat', 'providers.lng', 'providers.is_mobile', 'providers.service_radius_km', 'providers.commune_id', 'providers.name'])
                ->cursor() as $row) {
                $provider = [(float) $row->lat, (float) $row->lng, (int) $row->id, (string) $row->name, $row->service_radius_km ?? $radius];
                if ($row->is_mobile) {
                    $mobile[] = $provider;
                } else {
                    // ponytail: 0.25° grid cells (≥ 17 km wide in Belgium) so each commune only scans 3 x 3 cells; fine far beyond 100k pros.
                    $fixed[(int) floor($provider[0] * 4)][(int) floor($provider[1] * 4)][] = $provider;
                }
                $located[$row->commune_id] = ($located[$row->commune_id] ?? 0) + 1;
            }

            $pages = [];
            foreach ($communes as $commune) {
                $nearby = [];
                [$cy, $cx] = [(int) floor($commune['lat'] * 4), (int) floor($commune['lng'] * 4)];
                for ($y = $cy - 1; $y <= $cy + 1; $y++) {
                    for ($x = $cx - 1; $x <= $cx + 1; $x++) {
                        foreach ($fixed[$y][$x] ?? [] as $provider) {
                            $distance = $this->distance($commune['lat'], $commune['lng'], $provider[0], $provider[1]);
                            if ($distance <= $radius) {
                                $nearby[] = [$distance, $provider[3], $provider[2]];
                            }
                        }
                    }
                }
                // Travelling pros (usually few) reach as far as their own service radius.
                foreach ($mobile as $provider) {
                    if (abs($provider[0] - $commune['lat']) * self::KM_PER_DEGREE > $provider[4]) {
                        continue;
                    }
                    $distance = $this->distance($commune['lat'], $commune['lng'], $provider[0], $provider[1]);
                    if ($distance <= $provider[4]) {
                        $nearby[] = [$distance, $provider[3], $provider[2]];
                    }
                }

                if ($nearby === []) {
                    continue;
                }

                sort($nearby);
                $pages[$commune['id']] = [
                    'count' => count($nearby),
                    'first' => array_column(array_slice($nearby, 0, self::PAGE_SIZE), 2),
                    'size' => [$commune['population'] ?? -1, $located[$commune['id']] ?? 0, -$commune['id']],
                ];
            }

            $rows = [];
            foreach ($pages as $communeId => $page) {
                $rows[] = [
                    'category_id' => $categoryId,
                    'commune_id' => $communeId,
                    'providers_count' => $page['count'],
                    'is_indexable' => $page['count'] >= self::MIN_INDEXABLE && ! $this->duplicatesBiggerNeighbour($page, $pages, $neighbours[$communeId]),
                ];
            }

            DB::transaction(function () use ($categoryId, $rows) {
                DB::table('city_pages')->where('category_id', $categoryId)->delete();
                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('city_pages')->insert($chunk);
                }
            });
            $total += count($rows);
        }

        $this->info("{$total} city pages, ".DB::table('city_pages')->where('is_indexable', true)->count().' indexable.');

        return self::SUCCESS;
    }

    /**
     * Other communes whose centre lies within the page radius, per commune.
     *
     * @param  array<int, array{id: int, lat: float, lng: float, population: int|null}>  $communes
     * @return array<int, list<int>>
     */
    private function neighbours(array $communes, int $radius): array
    {
        $neighbours = array_fill_keys(array_keys($communes), []);

        foreach ($communes as $a) {
            foreach ($communes as $b) {
                if ($a['id'] < $b['id'] && $this->distance($a['lat'], $a['lng'], $b['lat'], $b['lng']) <= $radius) {
                    $neighbours[$a['id']][] = $b['id'];
                    $neighbours[$b['id']][] = $a['id'];
                }
            }
        }

        return $neighbours;
    }

    /**
     * Whether 70 % or more of this page's first results already appear first on a bigger neighbouring commune's page.
     *
     * @param  array{count: int, first: list<int>, size: array{int, int, int}}  $page
     * @param  array<int, array{count: int, first: list<int>, size: array{int, int, int}}>  $pages
     * @param  list<int>  $neighbourIds
     */
    private function duplicatesBiggerNeighbour(array $page, array $pages, array $neighbourIds): bool
    {
        foreach ($neighbourIds as $otherId) {
            $other = $pages[$otherId] ?? null;
            if ($other === null || $other['count'] < self::MIN_INDEXABLE || $this->isSmaller($other['size'], $page['size'])) {
                continue;
            }

            if (count(array_intersect($page['first'], $other['first'])) / count($page['first']) >= self::MAX_OVERLAP) {
                return true;
            }
        }

        return false;
    }

    /**
     * Population when both are known, then pros located in the commune, then the oldest record.
     *
     * @param  array{int, int, int}  $a
     * @param  array{int, int, int}  $b
     */
    private function isSmaller(array $a, array $b): bool
    {
        if ($a[0] >= 0 && $b[0] >= 0 && $a[0] !== $b[0]) {
            return $a[0] < $b[0];
        }

        return [$a[1], $a[2]] < [$b[1], $b[2]];
    }

    /**
     * Same equirectangular distance as ProviderSearch, so pages and precomputation agree.
     */
    private function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dy = ($lat2 - $lat1) * self::KM_PER_DEGREE;
        $dx = ($lng2 - $lng1) * self::KM_PER_DEGREE * cos(deg2rad($lat1));

        return sqrt($dy * $dy + $dx * $dx);
    }
}
