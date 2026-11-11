<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'province' => fake()->word(),
            'city' => fake()->city(),
            'address' => fake()->address(),
            'phone' => fake()->numerify('##########'),
            'is_default' => false,
            'status' => 'active',
        ];
    }
}
