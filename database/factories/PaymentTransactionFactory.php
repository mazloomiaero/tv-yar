<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Support\PaymentTransactionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'gateway' => 'zarinpal',
            'authority' => 'A'.fake()->unique()->numerify(str_repeat('#', 35)),
            'transaction_id' => '',
            'reference_id' => '',
            'request_data' => [],
            'response_data' => [],
            'status' => PaymentTransactionStatus::Initiated->value,
        ];
    }
}
