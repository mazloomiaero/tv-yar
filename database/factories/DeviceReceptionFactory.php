<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeviceReception;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceReception>
 */
class DeviceReceptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'receipt_number' => fake()->unique()->numerify('REC-1405-######'),
            'customer_id' => fn (array $attributes) => ServiceRequest::query()
                ->findOrFail($attributes['service_request_id'])
                ->customer_id,
            'device_id' => fn (array $attributes) => ServiceRequest::query()
                ->findOrFail($attributes['service_request_id'])
                ->device_id,
            'received_by' => null,
            'received_date' => fake()->date(),
            'appearance' => '',
            'accompanying_items' => '',
            'reason' => '',
            'notes' => '',
            'delivery_date' => null,
        ];
    }
}
