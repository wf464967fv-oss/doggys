<?php

namespace Database\Factories;

use App\Models\Commune;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commune>
 */
class CommuneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();
        $names = ['fr' => $name, 'nl' => $name, 'en' => $name, 'de' => $name];

        return [
            'ref' => (string) fake()->unique()->numberBetween(10000, 99999),
            'names' => $names,
            'slugs' => Commune::slugsFor($names),
            'lat' => fake()->latitude(49.6, 51.4),
            'lng' => fake()->longitude(2.6, 6.3),
            'population' => fake()->numberBetween(2000, 200000),
        ];
    }

    /**
     * Named commune with translated names (["fr" => "Liège", "nl" => "Luik", …]).
     *
     * @param  array<string, string>|string  $names
     */
    public function named(array|string $names): static
    {
        $names = is_string($names) ? array_fill_keys(['fr', 'nl', 'en', 'de'], $names) : $names + array_fill_keys(['fr', 'nl', 'en', 'de'], reset($names));

        return $this->state(fn () => ['names' => $names, 'slugs' => Commune::slugsFor($names)]);
    }
}
