<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProviderSource;
use App\Enums\ProviderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProviderRequest;
use App\Models\AdminAction;
use App\Models\Category;
use App\Models\Provider;
use App\Rules\BelgianCompanyNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Listings: search, full edit, manual creation, hide/publish and removal ("removed" keeps a tombstone so
 * the import never brings the listing back).
 */
class ProviderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ProviderStatus::class)],
        ]);

        $providers = Provider::query()
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(fn ($query) => $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%')
                    ->orWhere('postal_code', $search)
                    ->orWhere('bce_number', 'like', '%'.$search.'%'));
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->with('claimedBy:id,name')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.providers.index', ['providers' => $providers, 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('admin.providers.form', ['provider' => new Provider(['is_mobile' => false]), 'categories' => Category::query()->orderBy('sort')->get()]);
    }

    public function store(ProviderRequest $request): RedirectResponse
    {
        $provider = new Provider(['source' => ProviderSource::Manual]);
        $this->save($request, $provider);
        AdminAction::record('provider.create', $provider, $provider->name);

        return redirect()->route('admin.providers.edit', $provider)->with('status', __('admin.providers.created'));
    }

    public function edit(Provider $provider): View
    {
        return view('admin.providers.form', [
            'provider' => $provider->load(['categories', 'claimedBy:id,name,email']),
            'categories' => Category::query()->orderBy('sort')->get(),
        ]);
    }

    public function update(ProviderRequest $request, Provider $provider): RedirectResponse
    {
        $this->save($request, $provider);
        AdminAction::record('provider.update', $provider, implode(', ', array_keys($provider->getChanges())) ?: null);

        return redirect()->route('admin.providers.edit', $provider)->with('status', __('admin.providers.saved'));
    }

    /**
     * Hides a published listing, or publishes a hidden one.
     */
    public function toggle(Provider $provider): RedirectResponse
    {
        abort_if($provider->status === ProviderStatus::Removed, 409);

        $status = $provider->status === ProviderStatus::Active ? ProviderStatus::Hidden : ProviderStatus::Active;
        $provider->forceFill(['status' => $status])->save();
        AdminAction::record('provider.'.($status === ProviderStatus::Hidden ? 'hide' : 'publish'), $provider);

        return back()->with('status', __('admin.providers.'.$status->value));
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        $provider->forceFill(['status' => ProviderStatus::Removed])->save();
        AdminAction::record('provider.remove', $provider, $provider->name);

        return redirect()->route('admin.providers.index')->with('status', __('admin.providers.removed'));
    }

    private function save(ProviderRequest $request, Provider $provider): void
    {
        $data = $request->validated();

        $provider->fill(Arr::except($data, ['categories', 'status', 'verification_level']));
        $provider->forceFill([
            'status' => $data['status'],
            'verification_level' => $data['verification_level'],
            'service_radius_km' => $data['is_mobile'] ? $data['service_radius_km'] : null,
            'bce_number' => filled($data['bce_number'] ?? null) ? BelgianCompanyNumber::normalize($data['bce_number']) : null,
        ])->save();
        $provider->categories()->sync(Category::query()->whereIn('slug', $data['categories'])->pluck('id'));
    }
}
