<?php

namespace App\Http\Controllers;

use App\Enums\VerificationLevel;
use App\Models\Category;
use App\Models\Commune;
use App\Models\PostalCode;
use App\Models\Provider;
use App\Services\ProviderSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Indexable SEO pages: all categories, one category across Belgium, one category in one commune.
 * City pages exist per municipality (Commune); their indexability is precomputed by doggys:refresh-city-pages.
 */
class CategoryController extends Controller
{
    /** Radius of a city page around the commune centre. */
    public const CITY_RADIUS_KM = 15;

    /** Distance up to which other communes are suggested on a city page. */
    private const NEIGHBOUR_CITY_KM = 30;

    public function index(): View
    {
        // Counting every listing per category scans the table: the counts (plain array) are cached for 10 minutes.
        $counts = Cache::remember('directory.category-counts', now()->addMinutes(10), fn () => Category::query()
            ->withCount(['providers' => fn ($query) => $query->active()])
            ->pluck('providers_count', 'id')
            ->all());
        $categories = Category::query()->orderBy('sort')->get()
            ->each(fn (Category $category) => $category->setAttribute('providers_count', (int) ($counts[$category->id] ?? 0)));

        return view('directory.categories', [
            'available' => $categories->where('providers_count', '>', 0)->sortByDesc('providers_count')->values(),
            'comingSoon' => $categories->where('providers_count', 0)->values(),
            'total' => Cache::remember('directory.active-count', now()->addMinutes(10), fn () => Provider::query()->active()->count()),
        ]);
    }

    public function show(string $category): View
    {
        $category = Category::findByRouteSlug($category) ?? abort(404);

        $providers = $category->providers()
            ->active()
            ->with('categories')
            ->orderByDesc('reviews_count')
            ->orderByRaw("REPLACE(REPLACE(LOWER(name), '''', ''), '’', '')")
            ->paginate(24);
        abort_if($providers->currentPage() > max(1, $providers->lastPage()), 404);

        return view('directory.category', [
            'category' => $category,
            'providers' => $providers,
            'cities' => $this->indexableCities($category)->take(36),
            'communesCount' => $category->providers()->active()->distinct()->count('commune_id'),
            'related' => Category::query()->where('group', $category->group)->whereKeyNot($category->id)
                ->whereHas('providers', fn ($query) => $query->active())->orderBy('sort')->get(),
            'alternates' => $this->alternates($category),
        ]);
    }

    public function city(string $category, string $city): View|RedirectResponse
    {
        $category = Category::findByRouteSlug($category) ?? abort(404);
        $locale = app()->getLocale();
        $commune = Commune::query()->where('slugs->'.$locale, $city)->first();

        if (! $commune) {
            // A section of a commune (Glain, Klemskerke…) or another spelling: permanent redirect to its commune.
            $place = collect(PostalCode::placeIndex())->first(fn (array $place) => $place['commune_id'] && in_array($city, $place['slugs'], true));
            $commune = $place ? Commune::query()->find($place['commune_id']) : null;
            abort_unless($commune, 404);

            return redirect()->to(localized_route('categories.city', ['category' => $category->routeSlug(), 'city' => $commune->slug()]), 301);
        }

        [$lat, $lng] = [(float) $commune->lat, (float) $commune->lng];
        $area = fn () => ProviderSearch::query($lat, $lng, self::CITY_RADIUS_KM, $category);

        // One grouped pass gives the total, the figures of the introduction and the neighbouring communes.
        $groups = $area()->reorder()->select('commune_id')->selectRaw(
            'COUNT(*) AS total, '
            .'SUM(CASE WHEN verification_level != ? THEN 1 ELSE 0 END) AS verified, '
            .'SUM(CASE WHEN is_mobile = ? THEN 1 ELSE 0 END) AS mobile, '
            .'SUM(CASE WHEN opening_hours LIKE ? THEN 1 ELSE 0 END) AS saturday',
            [VerificationLevel::None->value, true, '%Sa%'],
        )->groupBy('commune_id')->toBase()->get();
        $total = (int) $groups->sum('total');
        abort_if($total === 0, 404);

        $providers = ProviderSearch::paginate($area(), $lat, $lng, total: $total);
        $closest = $providers->onFirstPage() ? $providers->first() : $area()->first();
        ProviderSearch::setDistances([$closest], $lat, $lng);

        $page = DB::table('city_pages')->where('category_id', $category->id)->where('commune_id', $commune->id)->first();

        return view('directory.city', [
            'category' => $category,
            'commune' => $commune,
            'cityName' => $commune->label(),
            'providers' => $providers,
            'total' => $total,
            'stats' => ['verified' => (int) $groups->sum('verified'), 'mobile' => (int) $groups->sum('mobile'), 'saturday' => (int) $groups->sum('saturday')],
            'closest' => $closest,
            'indexable' => (bool) $page?->is_indexable && $providers->onFirstPage(),
            'neighbourhoods' => $this->neighbourhoods($groups, $commune),
            'nearbyCities' => $this->indexableCities($category)
                ->reject(fn (array $item) => $item['commune']->id === $commune->id)
                ->map(fn (array $item) => $item + ['distance' => ProviderSearch::distanceKm($lat, $lng, (float) $item['commune']->lat, (float) $item['commune']->lng)])
                ->where('distance', '<=', self::NEIGHBOUR_CITY_KM)
                ->sortBy('distance')
                ->take(8),
            'otherCategories' => $this->otherCategories($category, $commune),
            'alternates' => $this->alternates($category, $commune),
        ]);
    }

    /**
     * Communes whose page for this category is indexable, most pros first: the only ones worth a link.
     *
     * @return Collection<int, array{commune: Commune, count: int}>
     */
    private function indexableCities(Category $category): Collection
    {
        $pages = DB::table('city_pages')
            ->where('category_id', $category->id)
            ->where('is_indexable', true)
            ->orderByDesc('providers_count')
            ->get(['commune_id', 'providers_count']);
        $communes = Commune::query()->whereKey($pages->pluck('commune_id'))->get()->keyBy('id');

        return $pages->map(fn ($page) => ['commune' => $communes[$page->commune_id], 'count' => (int) $page->providers_count])->values();
    }

    /**
     * Other communes where the listed pros are located (for the introduction), at most 3.
     *
     * @param  Collection<int, object{commune_id: int|null, total: int|string}>  $groups
     * @return Collection<int, string>
     */
    private function neighbourhoods(Collection $groups, Commune $commune): Collection
    {
        $ids = $groups->filter(fn ($group) => $group->commune_id !== null && (int) $group->commune_id !== $commune->id)
            ->sortByDesc(fn ($group) => (int) $group->total)->take(3)->pluck('commune_id')->map(fn ($id) => (int) $id)->values();

        return Commune::query()->whereKey($ids)->get()->sortBy(fn (Commune $other) => $ids->search($other->id))->map->label()->values();
    }

    /**
     * Other categories present around the commune whose own city page is indexable, with their count.
     *
     * @return Collection<int, array{category: Category, count: int}>
     */
    private function otherCategories(Category $category, Commune $commune): Collection
    {
        $indexable = DB::table('city_pages')->where('commune_id', $commune->id)->where('is_indexable', true)->pluck('providers_count', 'category_id');
        $categories = Category::query()->whereKey($indexable->keys())->whereKeyNot($category->id)->get();

        return $categories
            ->map(fn (Category $other) => ['category' => $other, 'count' => (int) $indexable[$other->id]])
            ->sortByDesc('count')
            ->take(6)
            ->values();
    }

    /**
     * URLs of the page in every locale (category and commune slugs are translated).
     *
     * @return array<string, string>
     */
    private function alternates(Category $category, ?Commune $commune = null): array
    {
        $locales = config('app.locales');

        return array_combine($locales, array_map(fn (string $locale) => $commune
            ? route($locale.'.categories.city', ['category' => $category->routeSlug($locale), 'city' => $commune->slug($locale)])
            : route($locale.'.categories.show', ['category' => $category->routeSlug($locale)]),
            $locales,
        ));
    }
}
