<?php

namespace App\Http\Controllers;

use App\Enums\ClaimStatus;
use App\Enums\ReviewStatus;
use App\Http\Requests\BadgeRequestRequest;
use App\Http\Requests\ProviderUpdateRequest;
use App\Models\BadgeRequest;
use App\Models\Category;
use App\Models\Provider;
use App\Notifications\RequestReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * Professional area: listings the professional manages, their editable fields, badge requests and replies to reviews.
 * Routes use the "pro" middleware; each listing action is authorised by ProviderPolicy::manage.
 */
class ProController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('pro.index', [
            'providers' => $user->claimedProviders()
                ->with(['categories', 'badgeRequests' => fn ($query) => $query->where('status', ClaimStatus::Pending)])
                ->withCount(['reviews as unanswered_reviews_count' => fn ($query) => $query->where('status', ReviewStatus::Published)->whereNull('provider_reply')])
                ->orderBy('name')
                ->get(),
            'claims' => $user->claims()->with('provider:id,name,slug,status')->latest()->get(),
        ]);
    }

    public function edit(Provider $provider): View
    {
        return view('pro.edit', [
            'provider' => $provider->load('categories'),
            'categories' => Category::query()->orderBy('sort')->get(),
        ]);
    }

    public function update(ProviderUpdateRequest $request, Provider $provider): RedirectResponse
    {
        $data = $request->validated();
        $provider->update(Arr::except($data, 'categories') + ['service_radius_km' => $data['is_mobile'] ? $data['service_radius_km'] : null]);
        $provider->categories()->sync(Category::query()->whereIn('slug', $data['categories'])->pluck('id'));

        return redirect()->to(localized_route('pro.edit', $provider))->with('status', __('pro.edit.saved'));
    }

    public function createBadgeRequest(Provider $provider): View
    {
        return view('pro.badge', [
            'provider' => $provider,
            'pending' => $provider->badgeRequests()->where('status', ClaimStatus::Pending)->exists(),
        ]);
    }

    public function storeBadgeRequest(BadgeRequestRequest $request, Provider $provider): RedirectResponse
    {
        if ($provider->isRegulated() || $provider->badgeRequests()->where('status', ClaimStatus::Pending)->exists()) {
            return redirect()->to(localized_route('pro.index'))->with('status', __('pro.badge.already_pending'));
        }

        $badgeRequest = new BadgeRequest($request->validated());
        $badgeRequest->forceFill([
            'provider_id' => $provider->id,
            'user_id' => $request->user()->id,
            'status' => ClaimStatus::Pending,
            'locale' => app()->getLocale(),
            'proof_path' => $request->file('proof')->store('proofs', 'local'),
        ])->save();

        $request->user()->notify(new RequestReceived('badge', $provider));

        return redirect()->to(localized_route('pro.index'))->with('status', __('pro.badge.sent'));
    }

    public function reviews(Provider $provider): View
    {
        return view('pro.reviews', [
            'provider' => $provider,
            'reviews' => $provider->reviews()->where('status', ReviewStatus::Published)->with('user:id,name')->latest()->paginate(10),
        ]);
    }
}
