<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Blue "licensed" badge request: registration number (Order of veterinarians, breeder or shelter licence…) and proof.
 */
class BadgeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('provider'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'regulatory_number' => ['required', 'string', 'max:64'],
            'message' => ['nullable', 'string', 'max:2000'],
            'proof' => ClaimRequest::PROOF_RULES,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ProviderUpdateRequest::fieldNames();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ClaimRequest::proofMessages();
    }
}
