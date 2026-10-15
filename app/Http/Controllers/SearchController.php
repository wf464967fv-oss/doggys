<?php

namespace App\Http\Controllers;

use App\Enums\VerificationLevel;
use App\Http\Requests\SearchProvidersRequest;
use App\Models\Category;
use App\Models\Provider;
use App\Services\ProviderSearch;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Internal search page (noindex): category + place or browser location, filters, list and map.
 */
class SearchController extends Controller
{
    public function __invoke(SearchProvidersRequest $request): View
    {
        $origin = $request->origin();
        $category = $request->category();
        $results = null;

        if (isset($origin['lat'])) {
            $results = ProviderSearch::search(
                $origin['lat'],
                $origin['lng'],
                $request->radius(),
                $category,
                mobileOnly: $request->boolean('mobile'),
                verifiedOnly: $request->boolean('verified'),
                minRating: $request->integer('min_rating') ?: null,
            );
        }

        $categories = Category::query()->orderBy('sort')->get();

        return view('directory.search', [
            'origin' => $origin,
            'category' => $category,
            'radius' => $request->radius(),
            'results' => $results,
            'categoryOptions' => Category::groupedOptions($categories),
            'widerRadius' => collect(ProviderSearch::RADII)->first(fn (int $radius) => $radius > $request->radius()),
            'filtersUsed' => $request->boolean('mobile') || $request->boolean('verified') || $request->filled('min_rating'),
            // A filter is only offered when at least one listing can match it (cached: these scan the table).
            'availableFilters' => Cache::remember('directory.available-filters', now()->addMinutes(10), fn () => [
                'mobile' => Provider::query()->active()->where('is_mobile', true)->exists(),
                'verified' => Provider::query()->active()->where('verification_level', '!=', VerificationLevel::None)->exists(),
                'rating' => Provider::query()->active()->where('reviews_count', '>', 0)->exists(),
            ]),
        ]);
    }
}
