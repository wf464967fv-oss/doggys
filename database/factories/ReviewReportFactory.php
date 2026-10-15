<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewReport>
 */
class ReviewReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'user_id' => User::factory(),
            'reason' => fake()->sentence(),
            'status' => ReportStatus::Pending,
            'handled_at' => null,
        ];
    }
}
