<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Technician;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Technician>
 */
class TechnicianFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'branch_id' => Branch::factory(),
            'user_id' => null,
        ];
    }
}
