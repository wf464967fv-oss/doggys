<?php

namespace Database\Factories;

use App\Enums\CategoryGroup;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'slug' => Str::slug($name),
            'group' => fake()->randomElement(CategoryGroup::cases()),
            'name' => ['fr' => $name, 'nl' => $name, 'en' => $name, 'de' => $name],
            'description' => null,
            'icon' => 'paw',
            'sort' => fake()->numberBetween(0, 100),
            'is_regulated' => false,
        ];
    }
}
