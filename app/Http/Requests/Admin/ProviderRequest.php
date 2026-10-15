<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProviderStatus;
use App\Enums\Region;
use App\Enums\VerificationLevel;
use App\Http\Requests\ProviderUpdateRequest;
use App\Rules\BelgianCompanyNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Full edit (or manual creation) of a listing by the admin, including legal name, address, position and badges.
 */
class ProviderRequest extends ProviderUpdateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('admin');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $provider = $this->route('provider');

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', Rule::unique('providers', 'slug')->ignore($provider)],
            'street' => ['nullable', 'string', 'max:150'],
            'house_number' => ['nullable', 'string', 'max:16'],
            'postal_code' => ['required', 'digits:4'],
            'city' => ['required', 'string', 'max:100'],
            'region' => ['nullable', Rule::enum(Region::class)],
            'lat' => ['required', 'numeric', 'between:49.4,51.6'],
            'lng' => ['required', 'numeric', 'between:2.5,6.5'],
            'bce_number' => ['nullable', 'string', 'max:20', new BelgianCompanyNumber],
            'regulatory_number' => ['nullable', 'string', 'max:64'],
            'status' => ['required', Rule::enum(ProviderStatus::class)],
            'verification_level' => ['required', Rule::enum(VerificationLevel::class)],
            'categories' => ['required', 'array', 'min:1', 'max:10'],
        ] + self::contactRules();
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if (blank($this->input('slug')) && is_string($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name').' '.$this->input('city'))]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return self::fieldNames() + [
            'slug' => __('admin.providers.slug'),
            'region' => __('admin.providers.region'),
            'lat' => __('admin.providers.lat'),
            'lng' => __('admin.providers.lng'),
            'status' => __('admin.providers.status'),
            'verification_level' => __('admin.providers.verification_level'),
        ];
    }
}
