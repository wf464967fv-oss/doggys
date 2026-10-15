<?php

namespace Database\Factories;

use App\Enums\DogSex;
use App\Models\Dog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dog>
 */
class DogFactory extends Factory
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
            'name' => fake()->randomElement(['Max', 'Luna', 'Rocky', 'Nala', 'Filou', 'Bella', 'Oscar', 'Pixie']),
            'breed' => fake()->randomElement(['Labrador', 'Border collie', 'Berger malinois', 'Teckel', 'Croisé']),
            'sex' => fake()->randomElement(DogSex::cases()),
            'birth_date' => fake()->dateTimeBetween('-14 years', '-2 months')->format('Y-m-d'),
            'microchip_number' => fake()->numerify('967000#########'),
            'is_neutered' => fake()->boolean(),
            'weight_kg' => fake()->randomFloat(1, 2, 60),
            'vaccinations' => [
                ['name' => 'DHPPi + L4', 'date' => fake()->date()],
                ['name' => 'Rage', 'date' => fake()->date()],
            ],
            'health_notes' => fake()->optional()->sentence(),
        ];
    }
}
