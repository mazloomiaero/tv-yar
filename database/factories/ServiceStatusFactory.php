<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceStatus>
 */
class ServiceStatusFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
