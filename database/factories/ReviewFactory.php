<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Provider;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider_id' => Provider::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'body' => fake()->paragraph(3),
            'status' => ReviewStatus::Published,
            'provider_reply' => null,
            'provider_replied_at' => null,
        ];
    }
}
