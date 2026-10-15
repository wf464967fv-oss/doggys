<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'locale' => 'fr',
            'translation_key' => Str::slug($title),
            'slug' => Str::slug($title),
            'title' => $title,
            'excerpt' => fake()->sentence(15),
            'body' => fake()->paragraphs(4, true),
            'meta_title' => null,
            'meta_description' => null,
            'category' => null,
            'published_at' => now()->subDay(),
            'reading_minutes' => fake()->numberBetween(2, 10),
        ];
    }
}
