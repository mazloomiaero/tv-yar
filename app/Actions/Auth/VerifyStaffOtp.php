<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\StaffOtpStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class VerifyStaffOtp
{
    public function __construct(private StaffOtpStore $otpStore) {}

    public function execute(string $mobile, string $otp): User
    {
        $matches = User::query()->where('mobile', $mobile)->get();
        $user = $matches->count() === 1 ? $matches->first() : null;

        if ($user === null || ! $this->otpStore->verify($mobile, $otp)) {
            Log::notice('Staff OTP verify failed.', [
                'user_id' => $user?->id,
            ]);

            throw ValidationException::withMessages([
                'otp' => __('The provided code is invalid.'),
            ]);
        }

        Auth::guard('customer')->logout();
        Auth::guard('web')->login($user);

        return $user;
    }
}
