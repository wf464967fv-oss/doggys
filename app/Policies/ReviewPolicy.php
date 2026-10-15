<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Only the author edits their review.
     */
    public function update(User $user, Review $review): bool
    {
        return $review->user_id === $user->id;
    }

    /**
     * Only the author deletes their review (admins moderate from the admin area).
     */
    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->id;
    }

    /**
     * Only the professional managing the reviewed listing answers it.
     */
    public function reply(User $user, Review $review): bool
    {
        return $user->isPro() && $review->provider?->claimed_by_user_id === $user->id;
    }

    /**
     * Anyone signed in may report a review, except its author.
     */
    public function report(User $user, Review $review): bool
    {
        return $review->user_id !== $user->id;
    }
}
