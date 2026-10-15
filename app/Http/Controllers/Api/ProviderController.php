<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProviderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchProvidersRequest;
use App\Http\Resources\ProviderResource;
use App\Models\PostalCode;
use App\Models\Provider;
use App\Services\ProviderSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Read-only JSON API (v1) over the directory, for the future mobile app and the search autocomplete.
 */
class ProviderController extends Controller
{
    public function index(SearchProvidersRequest $request): AnonymousResourceCollection
    {
        $this->useRequestedLocale($request);
        $origin = $request->origin();

        if (! isset($origin['lat'])) {
            throw ValidationException::withMessages([
                'location' => __($origin ? 'directory.search.unknown_place' : 'directory.search.location_required'),
            ]);
        }

        return ProviderResource::collection(ProviderSearch::paginate(ProviderSearch::nearby(
            $origin['lat'],
            $origin['lng'],
            $request->radius(),
            $request->category(),
            mobileOnly: $request->boolean('mobile'),
            verifiedOnly: $request->boolean('verified'),
            minRating: $request->integer('min_rating') ?: null,
        )))->additional(['meta' => ['origin' => $origin, 'radius_km' => $request->radius()]]);
    }

    public function show(Request $request, Provider $provider): ProviderResource
    {
        abort_unless($provider->status === ProviderStatus::Active, 404);
        $this->useRequestedLocale($request);

        return new ProviderResource($provider->load('categories'));
    }

    /**
     * Postal codes ("4000 Liège") or localities matching what the visitor typed, at most 8.
     */
    public function places(Request $request): JsonResponse
    {
        $this->useRequestedLocale($request);
        $query = Str::limit(trim((string) $request->query('q')), 60, '');

        if (preg_match('/^\d{1,4}$/', $query)) {
            $places = PostalCode::query()->where('code', 'like', $query.'%')->orderBy('code')->orderBy('id')->limit(8)->get()
                ->map(fn (PostalCode $row) => $row->code.' '.$row->label());
        } elseif (mb_strlen($query) >= 2) {
            $places = collect(ProviderSearch::cities(Str::slug($query), 8))
                ->map(fn (array $city) => $city['codes'][0].' '.$city['names'][app()->getLocale()]);
        } else {
            $places = collect();
        }

        return response()->json(['data' => $places->values()]);
    }

    private function useRequestedLocale(Request $request): void
    {
        $locales = config('app.locales');
        $requested = $request->query('lang');

        app()->setLocale(in_array($requested, $locales, true) ? $requested : ($request->getPreferredLanguage($locales) ?? $locales[0]));
    }
}
