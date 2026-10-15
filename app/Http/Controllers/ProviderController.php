<?php

namespace App\Http\Controllers;

use App\Enums\ProviderStatus;
use App\Enums\ReviewStatus;
use App\Http\Requests\ProviderReportRequest;
use App\Models\Provider;
use App\Services\ProviderSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Public provider profile and its "report an error / request removal" form.
 */
class ProviderController extends Controller
{
    public function show(Provider $provider): View
    {
        abort_unless($provider->status === ProviderStatus::Active, 404);

        $provider->load(['categories' => fn ($query) => $query->orderBy('sort')]);
        $category = $provider->categories->first();

        return view('directory.provider', [
            'provider' => $provider,
            'category' => $category,
            'reviews' => $provider->reviews()
                ->where('status', ReviewStatus::Published)
                ->with('user:id,name')
                ->latest()
                ->limit(10)
                ->get(),
            'similar' => ProviderSearch::nearby((float) $provider->lat, (float) $provider->lng, 25, $category, exceptId: $provider->id)->take(4),
        ]);
    }

    public function report(ProviderReportRequest $request, Provider $provider): RedirectResponse
    {
        abort_unless($provider->status === ProviderStatus::Active, 404);

        $provider->reports()->create($request->validated());

        return redirect()->to(localized_route('providers.show', $provider))->with('status', __('directory.report.sent'));
    }
}
