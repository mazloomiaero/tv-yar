<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Device;
use App\Models\DeviceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'device_type_id' => DeviceType::factory(),
            'brand_id' => Brand::factory(),
            'model' => fake()->bothify('Model-###'),
            'serial_number' => fake()->unique()->bothify('SN########'),
            'title' => fake()->words(3, true),
            'purchase_date' => fake()->date(),
            'warranty_status' => 'in_warranty',
            'warranty_until' => fake()->date(),
            'notes' => '',
        ];
    }
}
