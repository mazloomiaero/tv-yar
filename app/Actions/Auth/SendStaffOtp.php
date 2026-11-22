<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\Sms\FarazSmsClient;
use App\Support\FarazSmsCredentialsStore;
use App\Support\StaffOtpStore;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendStaffOtp
{
    public function __construct(
        private StaffOtpStore $otpStore,
        private FarazSmsClient $sms,
        private FarazSmsCredentialsStore $credentials,
    ) {}

    /**
     * @return bool True when exactly one matching staff user exists and an OTP was issued.
     */
    public function execute(string $mobile): bool
    {
        $matches = User::query()->where('mobile', $mobile)->get();

        if ($matches->count() !== 1) {
            return false;
        }

        $userId = (int) $matches->first()->id;
        $plain = $this->otpStore->issue($mobile);

        Log::info('Staff portal OTP issued.', [
            'user_id' => $userId,
        ]);

        if (! $this->credentials->isReadyForPattern()) {
            return true;
        }

        try {
            $this->sms->sendPatternOtp($mobile, $plain);
        } catch (Throwable) {
            Log::warning('Staff portal OTP SMS send failed.', [
                'user_id' => $userId,
            ]);
        }

        return true;
    }
}
