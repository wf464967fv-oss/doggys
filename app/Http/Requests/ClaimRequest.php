<?php

namespace App\Http\Requests;

use App\Enums\ClaimAction;
use App\Rules\BelgianCompanyNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * "This is my business" claim: BCE number, position, proof document and the wished outcome.
 */
class ClaimRequest extends FormRequest
{
    /**
     * Proof document: PDF, JPEG or PNG up to 5 MB. "mimes" and "mimetypes" both read the real
     * (sniffed) MIME type of the content, not the file name.
     *
     * @var list<string>
     */
    public const PROOF_RULES = ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return self::claimRules() + ['action' => ['required', Rule::enum(ClaimAction::class)]];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function claimRules(): array
    {
        return [
            'bce_number' => ['required', 'string', 'max:20', new BelgianCompanyNumber],
            'position' => ['required', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
            'proof' => self::PROOF_RULES,
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
        return self::proofMessages();
    }

    /**
     * @return array<string, string>
     */
    public static function proofMessages(): array
    {
        return ['proof.mimes' => __('pro.validation.proof'), 'proof.mimetypes' => __('pro.validation.proof')];
    }
}
