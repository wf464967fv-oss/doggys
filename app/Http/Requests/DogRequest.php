<?php

namespace App\Http\Requests;

use App\Enums\DogSex;
use App\Models\Dog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DogRequest extends FormRequest
{
    /**
     * Ownership of an existing dog is checked by the route's DogPolicy; any signed-in user may add dogs.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Empty vaccination rows (added but left blank) are ignored; the checkbox is absent when unticked.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_neutered' => $this->boolean('is_neutered'),
            'vaccinations' => array_values(array_filter(
                (array) $this->input('vaccinations', []),
                fn ($row) => is_array($row) && (filled($row['name'] ?? null) || filled($row['date'] ?? null)),
            )),
        ]);
    }

    /**
     * A new profile is refused once the account reached Dog::MAX_PER_USER dogs.
     *
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->route('dog') === null && $this->user()->dogs()->count() >= Dog::MAX_PER_USER) {
                    $validator->errors()->add('name', __('dogs.limit_reached', ['max' => Dog::MAX_PER_USER]));
                }
            },
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'breed' => ['nullable', 'string', 'max:120'],
            'sex' => ['nullable', Rule::enum(DogSex::class)],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today', 'after:1990-01-01'],
            'microchip_number' => ['nullable', 'string', 'alpha_num:ascii', 'max:32'],
            'is_neutered' => ['boolean'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.2', 'max:150'],
            'vaccinations' => ['array', 'max:50'],
            'vaccinations.*.name' => ['required', 'string', 'max:120'],
            'vaccinations.*.date' => ['nullable', 'date', 'before_or_equal:today'],
            'health_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
