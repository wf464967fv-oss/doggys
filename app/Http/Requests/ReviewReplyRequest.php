<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Public answer of the professional to a review of their listing (one answer per review, editable).
 * Errors go to a bag per review, so the right form shows them on a page listing several reviews.
 */
class ReviewReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reply', $this->route('review'));
    }

    protected function prepareForValidation(): void
    {
        $this->errorBag = 'reply'.$this->route('review')->id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['reply' => ['required', 'string', 'min:2', 'max:2000']];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['reply' => __('pro.reviews.reply')];
    }
}
