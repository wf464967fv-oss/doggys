<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Review form (creation on a provider profile, edit from "My reviews"). Errors go to the "review" bag so the
 * other forms of the profile page stay untouched. On creation, the publication limits are checked too.
 */
class ReviewRequest extends FormRequest
{
    protected $errorBag = 'review';

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
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        $provider = $this->route('provider');

        if (! $provider) {
            return [];
        }

        return [function (Validator $validator) use ($provider) {
            $reason = Review::blockReason($this->user(), $provider);

            if ($reason) {
                $validator->errors()->add('body', __('reviews.blocked.'.$reason, ['days' => Review::COOLDOWN_DAYS, 'limit' => Review::DAILY_LIMIT]));
            }
        }];
    }

    /**
     * Back to the review form itself, not to the top of the provider page.
     */
    protected function getRedirectUrl(): string
    {
        return parent::getRedirectUrl().($this->route('provider') ? '#reviews' : '');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'rating' => __('reviews.form.rating'),
            'body' => __('reviews.form.body'),
        ];
    }
}
