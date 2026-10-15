<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * "Add your business": a new listing (hidden until the admin approves it) with its ownership claim.
 */
class NewProviderRequest extends ProviderUpdateRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'street' => ['required', 'string', 'max:150'],
            'house_number' => ['nullable', 'string', 'max:16'],
            'postal_code' => ['required', 'digits:4', Rule::exists('postal_codes', 'code')],
            'city' => ['nullable', 'string', 'max:100'],
        ] + self::contactRules() + ClaimRequest::claimRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return parent::messages() + ClaimRequest::proofMessages() + ['postal_code.exists' => __('pro.validation.postal_code')];
    }
}
