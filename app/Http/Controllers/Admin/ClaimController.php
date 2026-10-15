<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimAction;
use App\Enums\ClaimStatus;
use App\Enums\ProviderStatus;
use App\Enums\VerificationLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DecisionRequest;
use App\Models\AdminAction;
use App\Models\Claim;
use App\Notifications\RequestDecided;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Claims of listings ("this is my business") and new listings added by professionals.
 */
class ClaimController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', Rule::enum(ClaimStatus::class)]])['status'] ?? ClaimStatus::Pending->value;

        return view('admin.claims.index', [
            'status' => $status,
            'claims' => Claim::query()->where('status', $status)
                ->with(['provider:id,name,slug,city,status,claimed_by_user_id', 'user:id,name,email'])
                ->oldest()->paginate(25)->withQueryString(),
        ]);
    }

    /**
     * The proof is shown through a temporary signed URL of the private disk (valid 10 minutes).
     */
    public function show(Claim $claim): View
    {
        $claim->load(['provider.categories', 'provider.claimedBy:id,name,email', 'user:id,name,email,created_at']);

        return view('admin.claims.show', [
            'claim' => $claim,
            'proofUrl' => $claim->proof_path ? Storage::disk('local')->temporaryUrl($claim->proof_path, now()->addMinutes(10)) : null,
            'otherClaims' => Claim::query()->where('provider_id', $claim->provider_id)->whereKeyNot($claim->id)->with('user:id,name')->latest()->get(),
        ]);
    }

    /**
     * "Manage": the professional becomes the owner of the listing (green badge if the BCE number was checked,
     * new listings are published). "Delete": the listing is removed and never imported again.
     */
    public function approve(DecisionRequest $request, Claim $claim): RedirectResponse
    {
        abort_unless($claim->status === ClaimStatus::Pending, 409);

        $provider = $claim->provider;
        $bceVerified = $request->boolean('bce_verified');

        DB::transaction(function () use ($claim, $provider, $request, $bceVerified) {
            if ($claim->action === ClaimAction::Delete) {
                $provider->forceFill(['status' => ProviderStatus::Removed])->save();
            } else {
                $provider->forceFill(array_filter([
                    'claimed_by_user_id' => $claim->user_id,
                    'status' => $claim->creates_listing ? ProviderStatus::Active : null,
                    'bce_number' => $bceVerified ? $claim->bce_number : null,
                    'verification_level' => $bceVerified && ! $provider->isRegulated() ? VerificationLevel::Verified : null,
                ]))->save();
            }

            $claim->forceFill([
                'status' => ClaimStatus::Approved,
                'admin_note' => $request->validated('note'),
                'handled_at' => now(),
            ])->save();

            AdminAction::record('claim.approve', $claim, $claim->action->value.($bceVerified ? ', BCE verified' : ''));
        });

        $claim->user->notify((new RequestDecided($this->kind($claim), true, $provider, $request->validated('note')))->locale($claim->locale));

        return redirect()->route('admin.claims.index')->with('status', __('admin.claims.approved'));
    }

    public function reject(DecisionRequest $request, Claim $claim): RedirectResponse
    {
        abort_unless($claim->status === ClaimStatus::Pending, 409);

        $claim->forceFill([
            'status' => ClaimStatus::Rejected,
            'admin_note' => $request->validated('note'),
            'handled_at' => now(),
        ])->save();
        AdminAction::record('claim.reject', $claim, $request->validated('note'));

        $claim->user->notify((new RequestDecided($this->kind($claim), false, $claim->provider, $request->validated('note')))->locale($claim->locale));

        return redirect()->route('admin.claims.index')->with('status', __('admin.claims.rejected'));
    }

    private function kind(Claim $claim): string
    {
        return $claim->creates_listing ? 'listing' : 'claim_'.$claim->action->value;
    }
}
