<?php

namespace App\Http\Controllers;

use App\Enums\ClaimAction;
use App\Enums\ClaimStatus;
use App\Enums\ProviderSource;
use App\Enums\ProviderStatus;
use App\Http\Requests\ClaimRequest;
use App\Http\Requests\NewProviderRequest;
use App\Models\Category;
use App\Models\Claim;
use App\Models\PostalCode;
use App\Models\Provider;
use App\Models\User;
use App\Notifications\RequestReceived;
use App\Rules\BelgianCompanyNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * "This is my business" (claim of an existing listing) and "Add your business" (new listing, hidden until
 * the admin approves the claim that comes with it). Both need a professional account ("pro" middleware).
 */
class ClaimController extends Controller
{
    public function create(Request $request, Provider $provider): View|RedirectResponse
    {
        abort_unless($provider->status === ProviderStatus::Active, 404);

        if ($provider->claimed_by_user_id === $request->user()->id) {
            return redirect()->to(localized_route('pro.edit', $provider));
        }

        return view('pro.claim', [
            'provider' => $provider,
            'pendingClaim' => $this->pendingClaim($request->user(), $provider),
        ]);
    }

    public function store(ClaimRequest $request, Provider $provider): RedirectResponse
    {
        abort_unless($provider->status === ProviderStatus::Active, 404);

        if ($this->pendingClaim($request->user(), $provider)) {
            throw ValidationException::withMessages(['form' => __('pro.claim.already_pending')]);
        }

        $this->createClaim($request, $provider, ClaimAction::from($request->validated('action')));

        return redirect()->to(localized_route('pro.index'))->with('status', __('pro.claim.sent'));
    }

    public function createListing(): View
    {
        return view('pro.listing-create', ['categories' => Category::query()->orderBy('sort')->get()]);
    }

    public function storeListing(NewProviderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $postalCodes = PostalCode::query()->where('code', $data['postal_code'])->get();

        DB::transaction(function () use ($request, $data, $postalCodes) {
            $provider = new Provider([
                ...collect($data)->only(['name', 'street', 'house_number', 'postal_code', 'description', 'phone', 'email',
                    'website', 'opening_hours', 'is_mobile', 'service_radius_km'])->all(),
                'city' => filled($data['city'] ?? null) ? $data['city'] : $postalCodes->first()->name,
                'region' => $postalCodes->first()->region,
                // Geocoded on the postal code centroid: the admin can refine the position when approving.
                'lat' => round((float) $postalCodes->avg('lat'), 7),
                'lng' => round((float) $postalCodes->avg('lng'), 7),
                'source' => ProviderSource::Manual,
            ]);
            $provider->slug = $this->uniqueSlug($data['name'].' '.$provider->city);
            $provider->forceFill(['status' => ProviderStatus::Hidden])->save();
            $provider->categories()->sync(Category::query()->whereIn('slug', $data['categories'])->pluck('id'));

            $this->createClaim($request, $provider, ClaimAction::Manage, createsListing: true);
        });

        return redirect()->to(localized_route('pro.index'))->with('status', __('pro.listing.sent'));
    }

    private function createClaim(ClaimRequest|NewProviderRequest $request, Provider $provider, ClaimAction $action, bool $createsListing = false): Claim
    {
        $claim = new Claim([
            'bce_number' => BelgianCompanyNumber::normalize($request->validated('bce_number')),
            'position' => $request->validated('position'),
            'message' => $request->validated('message'),
            'action' => $action,
        ]);
        $claim->forceFill([
            'provider_id' => $provider->id,
            'user_id' => $request->user()->id,
            'status' => ClaimStatus::Pending,
            'locale' => app()->getLocale(),
            'creates_listing' => $createsListing,
            // Random file name on the private disk; the extension comes from the sniffed MIME type.
            'proof_path' => $request->file('proof')->store('proofs', 'local'),
        ])->save();

        $request->user()->notify(new RequestReceived($createsListing ? 'listing' : 'claim', $provider));

        return $claim;
    }

    private function pendingClaim(User $user, Provider $provider): ?Claim
    {
        return $provider->claims()->where('user_id', $user->id)->where('status', ClaimStatus::Pending)->first();
    }

    private function uniqueSlug(string $text): string
    {
        $slug = Str::limit(Str::slug($text), 80, '');

        while (Provider::withTrashed()->where('slug', $slug)->exists()) {
            $slug = Str::limit(Str::slug($text), 80, '').'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }
}
