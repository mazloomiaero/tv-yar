<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\Customer;
use App\Services\Sms\FarazSmsClient;
use App\Support\CustomerOtpStore;
use App\Support\FarazSmsCredentialsStore;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendCustomerOtp
{
    public function __construct(
        private CustomerOtpStore $otpStore,
        private FarazSmsClient $sms,
        private FarazSmsCredentialsStore $credentials,
    ) {}

    /**
     * @return bool True when exactly one matching customer exists and an OTP was issued.
     */
    public function execute(string $mobile): bool
    {
        $matches = Customer::query()->where('mobile', $mobile)->get();

        if ($matches->count() !== 1) {
            return false;
        }

        $customerId = (int) $matches->first()->id;
        $plain = $this->otpStore->issue($mobile);

        Log::info('Customer portal OTP issued.', [
            'customer_id' => $customerId,
        ]);

        if (! $this->credentials->isReadyForPattern()) {
            return true;
        }

        try {
            $this->sms->sendPatternOtp($mobile, $plain);
        } catch (Throwable) {
            Log::warning('Customer portal OTP SMS send failed.', [
                'customer_id' => $customerId,
            ]);
        }

        return true;
    }
}
