<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_code' => fake()->unique()->numerify('CUS-########'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'mobile' => fake()->unique()->numerify('09#########'),
            'mobile_secondary' => fake()->numerify('09#########'),
            'email' => fake()->safeEmail(),
            'national_id' => null,
            'notes' => '',
            'status' => 'active',
            'user_id' => null,
        ];
    }
}
