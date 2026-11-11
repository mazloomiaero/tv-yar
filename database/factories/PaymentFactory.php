<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => '10.00',
            'method' => PaymentMethod::Cash->value,
            'status' => PaymentStatus::PendingConfirmation->value,
            'paid_at' => null,
            'reference_number' => fake()->unique()->numerify('CASH-######'),
            'recorded_by' => null,
            'receipt_image' => null,
        ];
    }
}
