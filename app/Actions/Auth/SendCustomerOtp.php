<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\Customer;
use App\Support\CustomerOtpStore;
use Illuminate\Support\Facades\Log;

final class SendCustomerOtp
{
    public function __construct(private CustomerOtpStore $otpStore) {}

    /**
     * Always behaves the same to the caller when the mobile is unknown or ambiguous.
     */
    public function execute(string $mobile): void
    {
        $matches = Customer::query()->where('mobile', $mobile)->get();

        if ($matches->count() !== 1) {
            return;
        }

        $this->otpStore->issue($mobile);

        Log::info('Customer portal OTP issued.', [
            'customer_id' => $matches->first()->id,
        ]);
    }
}
