<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Models\Payment;

final class CreatePayment
{
    /**
     * @param  array{
     *     invoice_id: int,
     *     amount: string,
     *     method: string,
     *     status: string,
     *     paid_at: mixed,
     *     reference_number: string,
     *     recorded_by: int|null,
     *     receipt_image: string|null
     * }  $attributes
     */
    public function execute(array $attributes): Payment
    {
        return Payment::query()->create($attributes);
    }
}
