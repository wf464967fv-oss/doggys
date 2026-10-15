<?php

namespace Database\Factories;

use App\Enums\ProviderReportType;
use App\Enums\ReportStatus;
use App\Models\Provider;
use App\Models\ProviderReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderReport>
 */
class ProviderReportFactory extends Factory
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
            'email' => fake()->optional()->safeEmail(),
            'type' => fake()->randomElement(ProviderReportType::cases()),
            'message' => fake()->sentence(),
            'status' => ReportStatus::Pending,
            'handled_at' => null,
        ];
    }
}
