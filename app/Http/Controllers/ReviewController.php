<?php

namespace App\Http\Controllers;

use App\Enums\ProviderStatus;
use App\Enums\ReviewStatus;
use App\Http\Requests\ReviewReplyRequest;
use App\Http\Requests\ReviewReportRequest;
use App\Http\Requests\ReviewRequest;
use App\Models\Provider;
use App\Models\Review;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Reviews: publication on a provider profile, "My reviews" in the account, reports and professional replies.
 * Ratings of the listing are recomputed by the Review model events.
 */
class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Provider $provider): RedirectResponse
    {
        abort_unless($provider->status === ProviderStatus::Active, 404);

        // Per-user lock + re-check inside it: two simultaneous POSTs cannot both pass the 30-day / daily limits.
        try {
            Cache::lock('review:'.$request->user()->id, 10)->block(3, function () use ($request, $provider) {
                if ($reason = Review::blockReason($request->user(), $provider)) {
                    throw ValidationException::withMessages(['body' => __('reviews.blocked.'.$reason, ['days' => Review::COOLDOWN_DAYS, 'limit' => Review::DAILY_LIMIT])])->errorBag('review');
                }

                $review = new Review($request->validated());
                $review->user()->associate($request->user());
                $provider->reviews()->save($review);
            });
        } catch (LockTimeoutException) {
            abort(429);
        }

        return redirect()->to(localized_route('providers.show', $provider).'#reviews')->with('status', __('reviews.flash.published'));
    }

    public function index(Request $request): View
    {
        return view('reviews.index', [
            'reviews' => $request->user()->reviews()->with('provider:id,name,slug,status')->latest()->paginate(10),
        ]);
    }

    public function edit(Review $review): View
    {
        return view('reviews.edit', ['review' => $review->load('provider:id,name,slug')]);
    }

    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $review->update($request->validated());

        return redirect()->to(localized_route('account.reviews'))->with('status', __('reviews.flash.updated'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->to(localized_route('account.reviews'))->with('status', __('reviews.flash.deleted'));
    }

    public function report(ReviewReportRequest $request, Review $review): RedirectResponse
    {
        abort_unless($review->status === ReviewStatus::Published && $review->provider->status === ProviderStatus::Active, 404);

        $back = localized_route('providers.show', $review->provider).'#review-'.$review->id;

        if ($review->reports()->where('user_id', $request->user()->id)->exists()) {
            return redirect()->to($back)->with('status', __('reviews.flash.already_reported'));
        }

        $report = $review->reports()->make($request->validated());
        $report->user()->associate($request->user());
        $report->save();

        return redirect()->to($back)->with('status', __('reviews.flash.reported'));
    }

    public function reply(ReviewReplyRequest $request, Review $review): RedirectResponse
    {
        $review->forceFill([
            'provider_reply' => $request->validated('reply'),
            'provider_replied_at' => now(),
        ])->save();

        return back()->with('status', __('pro.reviews.replied'));
    }
}
