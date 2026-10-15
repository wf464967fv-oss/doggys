<?php

namespace App\Http\Requests;

use App\Enums\ProviderReportType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * "Report an error / request removal" form of a provider profile (no account needed).
 * An e-mail address is required for removal requests so the owner can be contacted to confirm.
 */
class ProviderReportRequest extends FormRequest
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
            'type' => ['required', Rule::enum(ProviderReportType::class)],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'email' => ['nullable', 'required_if:type,'.ProviderReportType::RemovalRequest->value, 'email:rfc', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => __('directory.report.type'),
            'message' => __('directory.report.message'),
            'email' => __('directory.report.email'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['email.required_if' => __('directory.report.email_required')];
    }
}
