<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PostalCode;
use App\Models\Provider;
use App\Services\ProviderSearch;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Indexable SEO pages: all categories, one category across Belgium, one category in one city.
 */
class CategoryController extends Controller
{
    /** Radius of a city page around the city centre. */
    public const CITY_RADIUS_KM = 15;

    /** Distance up to which other cities are suggested on a city page. */
    private const NEIGHBOUR_CITY_KM = 30;

    public function index(): View
    {
        $categories = Category::query()
            ->withCount(['providers' => fn ($query) => $query->active()])
            ->orderBy('sort')
            ->get();

        return view('directory.categories', [
            'categoryGroups' => $categories->groupBy(fn (Category $category) => $category->group->value),
            'total' => Provider::query()->active()->count(),
        ]);
    }

    public function show(string $category): View
    {
        $category = Category::findByRouteSlug($category) ?? abort(404);
        $providers = $category->providers()->active()->with('categories')->orderBy('name')->get();

        return view('directory.category', [
            'category' => $category,
            'providers' => ProviderSearch::paginate($providers, 24),
            'cities' => $this->citiesWithProviders($providers),
            'related' => $this->relatedCategories($category),
            'alternates' => $this->alternates($category),
        ]);
    }

    public function city(string $category, string $city): View
    {
        $category = Category::findByRouteSlug($category) ?? abort(404);
        $city = PostalCode::cityForSlug($city) ?? abort(404);

        $area = ProviderSearch::nearby($city['lat'], $city['lng'], self::CITY_RADIUS_KM);
        $providers = $area->filter(fn (Provider $provider) => $provider->categories->contains('id', $category->id))->values();
        abort_if($providers->isEmpty(), 404);

        $categoryCities = $this->citiesWithProviders($category->providers()->active()->get(['providers.id', 'postal_code', 'city']));

        return view('directory.city', [
            'category' => $category,
            'city' => $city,
            'cityName' => $city['names'][app()->getLocale()],
            'providers' => ProviderSearch::paginate($providers),
            'total' => $providers->count(),
            'verifiedCount' => $providers->filter->isVerified()->count(),
            'closest' => $providers->first(),
            'neighbourhoods' => $this->citiesWithProviders($providers)->reject(fn (array $item) => $item['city']['key'] === $city['key'])->take(3),
            'nearbyCities' => $categoryCities
                ->reject(fn (array $item) => $item['city']['key'] === $city['key'])
                ->map(fn (array $item) => $item + ['distance' => ProviderSearch::distanceKm($city['lat'], $city['lng'], $item['city']['lat'], $item['city']['lng'])])
                ->where('distance', '<=', self::NEIGHBOUR_CITY_KM)
                ->sortBy('distance')
                ->take(8),
            'otherCategories' => $area->flatMap->categories
                ->reject(fn (Category $other) => $other->id === $category->id)
                ->groupBy('id')
                ->map(fn (Collection $group) => ['category' => $group->first(), 'count' => $group->count()])
                ->sortByDesc('count')
                ->take(6)
                ->values(),
            'alternates' => $this->alternates($category, $city),
        ]);
    }

    /**
     * Cities where the given providers are located, most providers first.
     *
     * @param  iterable<Provider>  $providers
     * @return Collection<string, array{city: array{key: string, names: array<string, string>, slugs: array<string, string>, lat: float, lng: float, codes: list<string>}, count: int}>
     */
    private function citiesWithProviders(iterable $providers): Collection
    {
        return collect($providers)
            ->map(fn (Provider $provider) => PostalCode::cityFor($provider->postal_code, $provider->city))
            ->filter()
            ->groupBy('key')
            ->map(fn (Collection $group) => ['city' => $group->first(), 'count' => $group->count()])
            ->sortBy([['count', 'desc'], [fn (array $a, array $b) => $a['city']['key'] <=> $b['city']['key']]]);
    }

    /**
     * Other categories of the same family.
     *
     * @return EloquentCollection<int, Category>
     */
    private function relatedCategories(Category $category): EloquentCollection
    {
        return Category::query()->where('group', $category->group)->whereKeyNot($category->id)->orderBy('sort')->get();
    }

    /**
     * URLs of the page in every locale (category and city slugs are translated).
     *
     * @param  array{slugs: array<string, string>}|null  $city
     * @return array<string, string>
     */
    private function alternates(Category $category, ?array $city = null): array
    {
        $locales = config('app.locales');

        return array_combine($locales, array_map(fn (string $locale) => $city
            ? route($locale.'.categories.city', ['category' => $category->routeSlug($locale), 'city' => $city['slugs'][$locale]])
            : route($locale.'.categories.show', ['category' => $category->routeSlug($locale)]),
            $locales,
        ));
    }
}
