<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DecisionRequest;
use App\Models\AdminAction;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Notifications\ReviewHidden;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Review moderation: pending reports first, then every review (search, hide, publish again, delete).
 */
class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ReviewStatus::class)],
        ]);

        return view('admin.reviews.index', [
            'filters' => $filters,
            'reports' => ReviewReport::query()->where('status', ReportStatus::Pending)
                ->with(['review.provider:id,name,slug', 'review.user:id,name', 'user:id,name'])
                ->oldest()->get(),
            'reviews' => Review::query()
                ->when($filters['q'] ?? null, fn ($query, string $search) => $query->where('body', 'like', '%'.$search.'%'))
                ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
                ->with(['provider:id,name,slug', 'user:id,name,email'])
                ->withCount(['reports' => fn ($query) => $query->where('status', ReportStatus::Pending)])
                ->latest()->paginate(25)->withQueryString(),
        ]);
    }

    /**
     * Hides the review (rating recomputed), closes its pending reports and tells the author why (DSA art. 17).
     */
    public function hide(DecisionRequest $request, Review $review): RedirectResponse
    {
        DB::transaction(function () use ($request, $review) {
            $review->forceFill(['status' => ReviewStatus::Hidden])->save();
            $review->reports()->where('status', ReportStatus::Pending)->update(['status' => ReportStatus::Handled, 'handled_at' => now()]);
            AdminAction::record('review.hide', $review, $request->validated('note'));
        });

        $review->user?->notify(new ReviewHidden($review, $request->validated('note')));

        return back()->with('status', __('admin.reviews.hidden'));
    }

    public function publish(Review $review): RedirectResponse
    {
        $review->forceFill(['status' => ReviewStatus::Published])->save();
        AdminAction::record('review.publish', $review);

        return back()->with('status', __('admin.reviews.published'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        AdminAction::record('review.delete', $review, $review->provider?->name);
        $review->delete();

        return back()->with('status', __('admin.reviews.deleted'));
    }

    /**
     * The report is unfounded: the review stays published.
     */
    public function dismissReport(ReviewReport $report): RedirectResponse
    {
        $report->forceFill(['status' => ReportStatus::Dismissed, 'handled_at' => now()])->save();
        AdminAction::record('review_report.dismiss', $report);

        return back()->with('status', __('admin.reports.closed'));
    }
}
