<?php

namespace App\Policies;

use App\Models\Dog;
use App\Models\User;

/**
 * Dog profiles are private: only their owner may see or change them (admins included, on purpose).
 */
class DogPolicy
{
    public function update(User $user, Dog $dog): bool
    {
        return $dog->user_id === $user->id;
    }

    public function delete(User $user, Dog $dog): bool
    {
        return $dog->user_id === $user->id;
    }
}
