<?php

namespace App\Http\Controllers;

use App\Enums\ProviderStatus;
use App\Enums\ReviewStatus;
use App\Http\Requests\ProviderReportRequest;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Services\ProviderSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public provider profile and its "report an error / request removal" form.
 */
class ProviderController extends Controller
{
    public function show(Request $request, Provider $provider): View
    {
        abort_unless($provider->status === ProviderStatus::Active, 404);

        $provider->load(['categories' => fn ($query) => $query->orderBy('sort')]);
        $category = $provider->categories->first();
        $user = $request->user();

        $reviews = $provider->reviews()
            ->where('status', ReviewStatus::Published)
            ->with('user:id,name')
            ->latest()
            ->latest('id')
            ->paginate(10)
            ->fragment('reviews');

        return view('directory.provider', [
            'provider' => $provider,
            'category' => $category,
            'reviews' => $reviews,
            // Review form state for the visitor: null = may publish, otherwise the reasons.* key ("guest", "cooldown"…).
            'reviewBlock' => $user ? Review::blockReason($user, $provider) : 'guest',
            'reportedReviewIds' => $user
                ? ReviewReport::query()->where('user_id', $user->id)->whereIn('review_id', $reviews->pluck('id'))->pluck('review_id')->all()
                : [],
            'similar' => $this->similar($provider, $category),
        ]);
    }

    public function report(ProviderReportRequest $request, Provider $provider): RedirectResponse
    {
        abort_unless($provider->status === ProviderStatus::Active, 404);

        $provider->reports()->create($request->validated());

        return redirect()->to(localized_route('providers.show', $provider))->with('status', __('directory.report.sent'));
    }

    /**
     * Up to 4 active listings of the same main category within 25 km (LIMIT in SQL).
     *
     * @return Collection<int, Provider>
     */
    private function similar(Provider $provider, ?Category $category): Collection
    {
        $similar = ProviderSearch::query((float) $provider->lat, (float) $provider->lng, 25, $category, exceptId: $provider->id)
            ->substantial()
            ->with('categories')
            ->limit(4)
            ->get();
        ProviderSearch::setDistances($similar, (float) $provider->lat, (float) $provider->lng);

        return $similar;
    }
}
