<?php

namespace Database\Factories;

use App\Enums\ProviderSource;
use App\Enums\ProviderStatus;
use App\Enums\Region;
use App\Enums\VerificationLevel;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => fake()->optional()->paragraph(),
            'street' => fake()->streetName(),
            'house_number' => (string) fake()->numberBetween(1, 250),
            'postal_code' => (string) fake()->numberBetween(1000, 9992),
            'city' => fake()->city(),
            'region' => fake()->randomElement(Region::cases()),
            'lat' => fake()->latitude(49.6, 51.4),
            'lng' => fake()->longitude(2.6, 6.3),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
            'website' => fake()->optional()->url(),
            'opening_hours' => null,
            'is_mobile' => false,
            'service_radius_km' => null,
            'bce_number' => null,
            'regulatory_number' => null,
            'verification_level' => VerificationLevel::None,
            'status' => ProviderStatus::Active,
            'plan' => 'free',
            'source' => ProviderSource::Manual,
            'source_ref' => null,
            'imported_at' => null,
        ];
    }

    /**
     * A professional travelling to the customer within the given radius.
     */
    public function mobile(int $radiusKm = 15): static
    {
        return $this->state(fn () => ['is_mobile' => true, 'service_radius_km' => $radiusKm]);
    }

    /**
     * Green "verified" badge (BCE number and identity checked).
     */
    public function verified(): static
    {
        return $this->state(fn () => [
            'verification_level' => VerificationLevel::Verified,
            'bce_number' => '0'.fake()->numerify('#########'),
        ]);
    }

    /**
     * Blue "regulated" badge (implies verified).
     */
    public function regulated(): static
    {
        return $this->verified()->state(fn () => [
            'verification_level' => VerificationLevel::Regulated,
            'regulatory_number' => fake()->bothify('??-#####'),
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['status' => ProviderStatus::Hidden]);
    }
}
