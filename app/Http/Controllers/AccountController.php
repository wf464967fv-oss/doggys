<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Dog;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        $user = $request->user()->load(['dogs', 'reviews.provider:id,name', 'claims.provider:id,name']);

        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
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
                'message' => $claim->message,
                'action' => $claim->action->value,
                'status' => $claim->status->value,
                'created_at' => $claim->created_at->toIso8601String(),
            ]),
            'contact_messages' => $user->contactMessages()->get(['subject', 'message', 'created_at']),
        ];

        $filename = 'doggys-'.now()->format('Y-m-d').'.json';

        return response()
            ->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    /**
     * GDPR erasure: deletes the account and, through foreign keys, its dogs, reviews and claims
     * (plus the private claim proof files).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = $request->user();
        $proofFiles = $user->claims()->whereNotNull('proof_path')->pluck('proof_path')->all();

        Auth::logout();
        $user->delete();
        Storage::disk('local')->delete($proofFiles);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(localized_route('home'))->with('status', __('account.deleted'));
    }
}
