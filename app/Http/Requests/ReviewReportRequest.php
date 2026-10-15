<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * "Report this review" (notice under DSA art. 16). Errors go to a bag per review ("report{id}").
 */
class ReviewReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('report', $this->route('review'));
    }

    protected function prepareForValidation(): void
    {
        $this->errorBag = 'report'.$this->route('review')->id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:10', 'max:1000']];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['reason' => __('reviews.report.reason')];
    }
}
