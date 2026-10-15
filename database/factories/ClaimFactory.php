<?php

namespace Database\Factories;

use App\Enums\ClaimAction;
use App\Enums\ClaimStatus;
use App\Enums\UserRole;
use App\Models\Claim;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Claim>
 */
class ClaimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'user_id' => User::factory()->state(['role' => UserRole::Pro]),
            'bce_number' => '0'.fake()->numerify('#########'),
            'message' => fake()->optional()->sentence(),
            'proof_path' => null,
            'status' => ClaimStatus::Pending,
            'action' => ClaimAction::Manage,
            'admin_note' => null,
            'handled_at' => null,
        ];
    }
}
