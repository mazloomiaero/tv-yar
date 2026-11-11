<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\Customer;
use App\Support\CustomerOtpStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class VerifyCustomerOtp
{
    public function __construct(private CustomerOtpStore $otpStore) {}

    public function execute(string $mobile, string $otp): Customer
    {
        $matches = Customer::query()->where('mobile', $mobile)->get();
        $customer = $matches->count() === 1 ? $matches->first() : null;

        if ($customer === null || ! $this->otpStore->verify($mobile, $otp)) {
            Log::notice('Customer OTP verify failed.', [
                'customer_id' => $customer?->id,
            ]);

            throw ValidationException::withMessages([
                'otp' => __('The provided code is invalid.'),
            ]);
        }

        Auth::guard('web')->logout();
        Auth::guard('customer')->login($customer);

        return $customer;
    }
}
