<?php

namespace App\Policies;

use App\Enums\ProviderStatus;
use App\Models\Provider;
use App\Models\User;

class ProviderPolicy
{
    /**
     * A professional manages (edits, requests a badge for) only the listings the admin assigned to them.
     */
    public function manage(User $user, Provider $provider): bool
    {
        return $user->isPro() && $provider->claimed_by_user_id === $user->id
            && $provider->status !== ProviderStatus::Removed;
    }
}
