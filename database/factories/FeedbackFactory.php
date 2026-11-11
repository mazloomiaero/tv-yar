<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'score' => 'good',
            'comment' => fake()->sentence(),
        ];
    }
}
