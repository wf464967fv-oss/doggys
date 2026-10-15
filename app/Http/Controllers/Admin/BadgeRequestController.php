<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
use App\Enums\VerificationLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DecisionRequest;
use App\Models\AdminAction;
use App\Models\BadgeRequest;
use App\Notifications\RequestDecided;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Blue "licensed" badge requests (regulated professions).
 */
class BadgeRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', Rule::enum(ClaimStatus::class)]])['status'] ?? ClaimStatus::Pending->value;

        return view('admin.badges.index', [
            'status' => $status,
            'badgeRequests' => BadgeRequest::query()->where('status', $status)
                ->with(['provider:id,name,slug,city,verification_level', 'user:id,name,email'])
                ->oldest()->paginate(25)->withQueryString(),
        ]);
    }

    public function show(BadgeRequest $badgeRequest): View
    {
        $badgeRequest->load(['provider.categories', 'user:id,name,email']);

        return view('admin.badges.show', [
            'badgeRequest' => $badgeRequest,
            'proofUrl' => $badgeRequest->proof_path ? Storage::disk('local')->temporaryUrl($badgeRequest->proof_path, now()->addMinutes(10)) : null,
        ]);
    }

    /**
     * The blue badge implies the green one.
     */
    public function approve(DecisionRequest $request, BadgeRequest $badgeRequest): RedirectResponse
    {
        abort_unless($badgeRequest->status === ClaimStatus::Pending, 409);

        DB::transaction(function () use ($request, $badgeRequest) {
            $badgeRequest->provider->forceFill([
                'verification_level' => VerificationLevel::Regulated,
                'regulatory_number' => $badgeRequest->regulatory_number,
            ])->save();

            $badgeRequest->forceFill([
                'status' => ClaimStatus::Approved,
                'admin_note' => $request->validated('note'),
                'handled_at' => now(),
            ])->save();

            AdminAction::record('badge.approve', $badgeRequest, $badgeRequest->regulatory_number);
        });

        $badgeRequest->user->notify((new RequestDecided('badge', true, $badgeRequest->provider, $request->validated('note')))->locale($badgeRequest->locale));

        return redirect()->route('admin.badges.index')->with('status', __('admin.badges.approved'));
    }

    public function reject(DecisionRequest $request, BadgeRequest $badgeRequest): RedirectResponse
    {
        abort_unless($badgeRequest->status === ClaimStatus::Pending, 409);

        $badgeRequest->forceFill([
            'status' => ClaimStatus::Rejected,
            'admin_note' => $request->validated('note'),
            'handled_at' => now(),
        ])->save();
        AdminAction::record('badge.reject', $badgeRequest, $request->validated('note'));

        $badgeRequest->user->notify((new RequestDecided('badge', false, $badgeRequest->provider, $request->validated('note')))->locale($badgeRequest->locale));

        return redirect()->route('admin.badges.index')->with('status', __('admin.badges.rejected'));
    }
}
