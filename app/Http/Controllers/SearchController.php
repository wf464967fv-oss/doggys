<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchProvidersRequest;
use App\Models\Category;
use App\Services\ProviderSearch;
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
            $results = ProviderSearch::paginate(ProviderSearch::nearby(
                $origin['lat'],
                $origin['lng'],
                $request->radius(),
                $category,
                mobileOnly: $request->boolean('mobile'),
                verifiedOnly: $request->boolean('verified'),
                minRating: $request->integer('min_rating') ?: null,
            ));
        }

        $categories = Category::query()->orderBy('sort')->get();

        return view('directory.search', [
            'origin' => $origin,
            'category' => $category,
            'radius' => $request->radius(),
            'results' => $results,
            'categories' => $categories,
            'categoryOptions' => Category::groupedOptions($categories),
            'widerRadius' => collect(ProviderSearch::RADII)->first(fn (int $radius) => $radius > $request->radius()),
        ]);
    }
}
