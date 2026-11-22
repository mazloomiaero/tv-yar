<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Staff SMS login codes live in Cache only — not a database table.
 */
final class StaffOtpStore
{
    public const TTL_SECONDS = 300;

    public const MAX_ATTEMPTS = 5;

    public function issue(string $mobile): string
    {
        $plain = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->key($mobile), [
            'hash' => Hash::make($plain),
            'attempts' => 0,
        ], self::TTL_SECONDS);

        return $plain;
    }

    public function verify(string $mobile, string $plain): bool
    {
        $payload = Cache::get($this->key($mobile));

        if (! is_array($payload) || ! isset($payload['hash'], $payload['attempts'])) {
            return false;
        }

        $attempts = (int) $payload['attempts'] + 1;

        if ($attempts > self::MAX_ATTEMPTS) {
            $this->forget($mobile);

            return false;
        }

        $matches = Hash::check($plain, (string) $payload['hash']);

        if ($matches) {
            $this->forget($mobile);

            return true;
        }

        Cache::put($this->key($mobile), [
            'hash' => $payload['hash'],
            'attempts' => $attempts,
        ], self::TTL_SECONDS);

        return false;
    }

    public function forget(string $mobile): void
    {
        Cache::forget($this->key($mobile));
    }

    public function key(string $mobile): string
    {
        return 'staff-otp:'.$mobile;
    }
}
