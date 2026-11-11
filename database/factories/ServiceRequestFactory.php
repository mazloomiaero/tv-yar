<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Device;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Support\ServiceRequestSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_number' => fake()->unique()->numerify('SR-1405-######'),
            'customer_id' => Customer::factory(),
            'device_id' => fn (array $attributes) => Device::factory()->create([
                'customer_id' => $attributes['customer_id'],
            ])->id,
            'service_type_id' => ServiceType::factory(),
            'address_id' => fn (array $attributes) => CustomerAddress::factory()->create([
                'customer_id' => $attributes['customer_id'],
            ])->id,
            'status_id' => ServiceStatus::factory(),
            'source' => ServiceRequestSource::Phone->value,
            'priority' => 'normal',
            'problem_description' => fake()->sentence(),
            'preferred_date' => fake()->date(),
            'preferred_start_time' => '09:00:00',
            'preferred_end_time' => '12:00:00',
            'notes' => '',
            'created_by' => null,
            'closed_at' => null,
        ];
    }
}
