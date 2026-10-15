<?php

namespace Database\Factories;

use App\Enums\Region;
use App\Models\PostalCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostalCode>
 */
class PostalCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => (string) fake()->numberBetween(1000, 9992),
            'name' => fake()->unique()->city(),
            'name_translations' => null,
            'lat' => fake()->latitude(49.6, 51.4),
            'lng' => fake()->longitude(2.6, 6.3),
            'province' => null,
            'region' => fake()->randomElement(Region::cases()),
        ];
    }
}
