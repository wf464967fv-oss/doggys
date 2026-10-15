<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Fields a professional may edit on the listing they manage. Legal name and address only change through
 * the admin. The rules are shared with the "add your business" and admin listing forms.
 */
class ProviderUpdateRequest extends FormRequest
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
        return self::contactRules();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function contactRules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'regex:/^\+\d{8,15}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'is_mobile' => ['boolean'],
            'service_radius_km' => ['nullable', 'required_if:is_mobile,1', 'integer', 'between:1,100'],
            'categories' => ['required', 'array', 'min:1', 'max:3'],
            'categories.*' => ['string', Rule::exists('categories', 'slug')],
        ];
    }

    /**
     * Belgian phone numbers are stored in international format (0478 12 34 56 => +32478123456).
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_mobile' => $this->boolean('is_mobile')]);

        $phone = $this->input('phone');

        if (is_string($phone) && filled($phone)) {
            $phone = (string) preg_replace('/[^\d+]/', '', $phone);
            $this->merge(['phone' => match (true) {
                str_starts_with($phone, '00') => '+'.substr($phone, 2),
                str_starts_with($phone, '0') => '+32'.substr($phone, 1),
                default => $phone,
            }]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return self::fieldNames();
    }

    /**
     * @return array<string, string>
     */
    public static function fieldNames(): array
    {
        return collect([
            'name', 'description', 'phone', 'email', 'website', 'opening_hours', 'is_mobile', 'service_radius_km', 'categories',
            'street', 'house_number', 'postal_code', 'city', 'bce_number', 'position', 'message', 'proof', 'action', 'regulatory_number',
        ])->mapWithKeys(fn (string $field) => [$field => __('pro.fields.'.$field)])->all();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['phone.regex' => __('pro.validation.phone')];
    }
}
