<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/**
 * Installation FarazSMS secrets outside the 41-table schema.
 * Encrypted file under the local disk (storage/app/private); falls back to config/env.
 */
final class FarazSmsCredentialsStore
{
    public const PATH = 'farazsms.json';

    /**
     * @return array{api_key: string, pattern_code: string, otp_param: string, line_number: string}
     */
    public function resolved(): array
    {
        $stored = $this->readStored();
        $envKey = trim((string) config('services.farazsms.api_key', ''));
        $envPattern = trim((string) config('services.farazsms.pattern_code', ''));
        $envParam = trim((string) config('services.farazsms.otp_param', 'code'));
        $envLine = trim((string) config('services.farazsms.line_number', '90008361'));

        return [
            'api_key' => $stored['api_key'] !== '' ? $stored['api_key'] : $envKey,
            'pattern_code' => $stored['pattern_code'] !== '' ? $stored['pattern_code'] : $envPattern,
            'otp_param' => $stored['otp_param'] !== '' ? $stored['otp_param'] : ($envParam !== '' ? $envParam : 'code'),
            'line_number' => $stored['line_number'] !== '' ? $stored['line_number'] : ($envLine !== '' ? $envLine : '90008361'),
        ];
    }

    /**
     * @return array{api_key: string, pattern_code: string, otp_param: string, line_number: string}
     */
    public function stored(): array
    {
        return $this->readStored();
    }

    public function hasApiKey(): bool
    {
        return $this->resolved()['api_key'] !== '';
    }

    public function isReadyForPattern(): bool
    {
        $credentials = $this->resolved();

        return $credentials['api_key'] !== '' && $credentials['pattern_code'] !== '';
    }

    /**
     * @param  array{api_key?: string|null, pattern_code?: string|null, otp_param?: string|null, line_number?: string|null}  $input
     */
    public function update(array $input): void
    {
        $current = $this->readStored();

        $apiKey = array_key_exists('api_key', $input) ? trim((string) ($input['api_key'] ?? '')) : '';
        if ($apiKey !== '') {
            $current['api_key'] = $apiKey;
        }

        if (array_key_exists('pattern_code', $input)) {
            $current['pattern_code'] = trim((string) ($input['pattern_code'] ?? ''));
        }

        if (array_key_exists('otp_param', $input)) {
            $param = trim((string) ($input['otp_param'] ?? ''));
            $current['otp_param'] = $param !== '' ? $param : 'code';
        }

        if (array_key_exists('line_number', $input)) {
            $line = trim((string) ($input['line_number'] ?? ''));
            $current['line_number'] = $line !== '' ? $line : '90008361';
        }

        $payload = json_encode($current, JSON_THROW_ON_ERROR);
        Storage::disk('local')->put(self::PATH, Crypt::encryptString($payload));
    }

    /**
     * @return array{api_key: string, pattern_code: string, otp_param: string, line_number: string}
     */
    private function readStored(): array
    {
        $defaults = [
            'api_key' => '',
            'pattern_code' => '',
            'otp_param' => '',
            'line_number' => '',
        ];

        if (! Storage::disk('local')->exists(self::PATH)) {
            return $defaults;
        }

        try {
            $plain = Crypt::decryptString(Storage::disk('local')->get(self::PATH) ?? '');
            $decoded = json_decode($plain, true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException|\TypeError) {
            return $defaults;
        }

        if (! is_array($decoded)) {
            return $defaults;
        }

        return [
            'api_key' => trim((string) ($decoded['api_key'] ?? '')),
            'pattern_code' => trim((string) ($decoded['pattern_code'] ?? '')),
            'otp_param' => trim((string) ($decoded['otp_param'] ?? '')),
            'line_number' => trim((string) ($decoded['line_number'] ?? '')),
        ];
    }
}
