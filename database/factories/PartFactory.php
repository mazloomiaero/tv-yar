<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Part;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Part>
 */
class PartFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => fake()->optional()->bothify('P-####'),
            'default_unit_price' => '1000.00',
            'unit' => 'pcs',
            'status' => 'active',
        ];
    }
}
