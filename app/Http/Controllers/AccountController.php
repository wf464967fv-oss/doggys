<?php

namespace App\Http\Controllers;

use App\Models\BadgeRequest;
use App\Models\Claim;
use App\Models\ContactMessage;
use App\Models\Dog;
use App\Models\Provider;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        return view('account.dashboard', [
            'user' => $request->user(),
            'dogs' => $request->user()->dogs()->orderBy('name')->get(),
        ]);
    }

    public function profile(Request $request): View
    {
        return view('account.profile', ['user' => $request->user()]);
    }

    /**
     * GDPR data portability: everything Doggys stores about the user, as a JSON download.
     */
    public function export(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'dogs', 'reviews.provider:id,name', 'claims.provider:id,name', 'badgeRequests.provider:id,name', 'claimedProviders', 'reviewReports', 'contactMessages',
        ]);

        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'pending_email' => $user->pending_email,
                'role' => $user->role->value,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at->toIso8601String(),
            ],
            'dogs' => $user->dogs->map(fn (Dog $dog) => [
                'name' => $dog->name,
                'breed' => $dog->breed,
                'sex' => $dog->sex?->value,
                'birth_date' => $dog->birth_date?->toDateString(),
                'microchip_number' => $dog->microchip_number,
                'is_neutered' => $dog->is_neutered,
                'weight_kg' => $dog->weight_kg,
                'vaccinations' => $dog->vaccinations ?? [],
                'health_notes' => $dog->health_notes,
                'created_at' => $dog->created_at->toIso8601String(),
            ]),
            'reviews' => $user->reviews->map(fn (Review $review) => [
                'provider' => $review->provider?->name,
                'rating' => $review->rating,
                'body' => $review->body,
                'status' => $review->status->value,
                'provider_reply' => $review->provider_reply,
                'created_at' => $review->created_at->toIso8601String(),
            ]),
            'claims' => $user->claims->map(fn (Claim $claim) => [
                'provider' => $claim->provider?->name,
                'bce_number' => $claim->bce_number,
                'position' => $claim->position,
                'message' => $claim->message,
                'action' => $claim->action->value,
                'status' => $claim->status->value,
                'created_at' => $claim->created_at->toIso8601String(),
            ]),
            'badge_requests' => $user->badgeRequests->map(fn (BadgeRequest $badgeRequest) => [
                'provider' => $badgeRequest->provider?->name,
                'regulatory_number' => $badgeRequest->regulatory_number,
                'message' => $badgeRequest->message,
                'status' => $badgeRequest->status->value,
                'created_at' => $badgeRequest->created_at->toIso8601String(),
            ]),
            'claimed_providers' => $user->claimedProviders->map(fn (Provider $provider) => [
                'name' => $provider->name,
                'slug' => $provider->slug,
                'verification_level' => $provider->verification_level->value,
                'plan' => $provider->plan,
            ]),
            'review_reports' => $user->reviewReports->map(fn (ReviewReport $report) => [
                'review_id' => $report->review_id,
                'reason' => $report->reason,
                'status' => $report->status->value,
                'created_at' => $report->created_at->toIso8601String(),
            ]),
            'contact_messages' => $user->contactMessages->map(fn (ContactMessage $message) => [
                'subject' => $message->subject,
                'message' => $message->message,
                'created_at' => $message->created_at->toIso8601String(),
            ]),
        ];

        $filename = 'doggys-'.now()->format('Y-m-d').'.json';

        return response()
            ->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    /**
     * Confirms a pending e-mail change from the signed link sent to the new address.
     */
    public function confirmEmail(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            (string) $user->getKey() === $id && $user->pending_email && hash_equals(sha1($user->pending_email), $hash),
            403,
        );

        if (User::where('email', $user->pending_email)->exists()) {
            $user->forceFill(['pending_email' => null])->save();

            return redirect()->to(localized_route('account.profile'))->with('error', __('account.email_taken'));
        }

        $user->forceFill([
            'email' => $user->pending_email,
            'pending_email' => null,
            'email_verified_at' => now(),
        ])->save();

        return redirect()->to(localized_route('account.profile'))->with('status', __('account.email_changed'));
    }

    /**
     * GDPR erasure of the signed-in account (see User::purge() for what is deleted).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = $request->user();

        // Log out first: logging out afterwards would re-save the user while cycling its remember token.
        Auth::logout();

        $user->purge();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(localized_route('home'))->with('status', __('account.deleted'));
    }
}
