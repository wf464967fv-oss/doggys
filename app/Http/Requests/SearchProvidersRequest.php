<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Services\ProviderSearch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query string of the provider search (web page and /api/v1/providers).
 */
class SearchProvidersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Empty form fields arrive as "" and must not trip the rules.
     */
    protected function prepareForValidation(): void
    {
        $this->replace(array_filter($this->query(), fn ($value) => $value !== '' && $value !== null));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', Rule::exists(Category::class, 'slug')],
            'location' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric', 'between:49,52', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:2,7', 'required_with:lat'],
            'radius' => ['nullable', 'integer', Rule::in(ProviderSearch::RADII)],
            'mobile' => ['nullable', 'boolean'],
            'verified' => ['nullable', 'boolean'],
            'min_rating' => ['nullable', 'integer', 'between:1,5'],
            'near_me' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Where to search from: browser coordinates first, then the typed postal code or locality.
     * Null when nothing was given; "label" is null when the typed place is unknown.
     *
     * @return array{lat: float, lng: float, label: string}|array{label: null}|null
     */
    public function origin(): ?array
    {
        if ($this->filled(['lat', 'lng'])) {
            return ['lat' => (float) $this->input('lat'), 'lng' => (float) $this->input('lng'), 'label' => __('directory.search.near_you')];
        }

        if ($this->filled('location')) {
            return ProviderSearch::locate($this->string('location')) ?? ['label' => null];
        }

        return null;
    }

    /**
     * Invalid web searches land on the empty search page, which shows the error.
     */
    protected function getRedirectUrl(): string
    {
        return localized_route('search');
    }

    public function category(): ?Category
    {
        return $this->filled('category') ? Category::query()->where('slug', $this->input('category'))->first() : null;
    }

    public function radius(): int
    {
        return (int) $this->input('radius', ProviderSearch::DEFAULT_RADIUS);
    }
}
