<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Support\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => fake()->unique()->numerify('INV-1405-######'),
            'service_request_id' => ServiceRequest::factory(),
            'customer_id' => fn (array $attributes) => ServiceRequest::query()
                ->findOrFail($attributes['service_request_id'])
                ->customer_id,
            'customer_name' => fake()->name(),
            'customer_mobile' => fake()->numerify('09#########'),
            'customer_address' => '',
            'subtotal' => '0.00',
            'discount' => '0.00',
            'tax' => '0.00',
            'total' => '0.00',
            'status' => InvoiceStatus::PendingPayment->value,
            'issued_at' => now(),
            'paid_at' => null,
        ];
    }
}
