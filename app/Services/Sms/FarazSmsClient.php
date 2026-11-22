<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Support\FarazSmsCredentialsStore;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class FarazSmsClient
{
    public function __construct(private FarazSmsCredentialsStore $credentials) {}

    public function sendPatternOtp(string $mobile, string $code): void
    {
        if (! $this->credentials->isReadyForPattern()) {
            throw new RuntimeException('FarazSMS pattern send is not configured.');
        }

        $resolved = $this->credentials->resolved();

        try {
            $response = $this->http($resolved['api_key'])
                ->asJson()
                ->post('/ws/v1/sms/pattern', [
                    'code' => $resolved['pattern_code'],
                    'recipient' => $mobile,
                    'attributes' => [
                        $resolved['otp_param'] => $code,
                    ],
                    'line_number' => $resolved['line_number'],
                    'number_format' => 'english',
                ])
                ->throw()
                ->json();
        } catch (ConnectionException $exception) {
            Log::warning('FarazSMS pattern connection failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('FarazSMS pattern connection failed.', 0, $exception);
        } catch (RequestException $exception) {
            Log::warning('FarazSMS pattern HTTP failed.', [
                'http_status' => $exception->response?->status(),
            ]);

            throw new RuntimeException('FarazSMS pattern HTTP failed.', 0, $exception);
        }

        if (is_array($response) && ($response['status'] ?? null) === 'error') {
            Log::warning('FarazSMS pattern rejected.');

            throw new RuntimeException('FarazSMS pattern rejected.');
        }
    }

    public function credit(): ?float
    {
        if (! $this->credentials->hasApiKey()) {
            return null;
        }

        $resolved = $this->credentials->resolved();

        try {
            $httpResponse = $this->http($resolved['api_key'])
                ->connectTimeout(5)
                ->timeout(20)
                ->get('/ws/v1/account/balance');
        } catch (ConnectionException $exception) {
            Log::warning('FarazSMS balance connection failed.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        } catch (\Throwable $exception) {
            Log::warning('FarazSMS balance lookup failed.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if ($httpResponse->failed()) {
            Log::warning('FarazSMS balance HTTP failed.', [
                'http_status' => $httpResponse->status(),
            ]);

            return null;
        }

        $response = $httpResponse->json();

        if (! is_array($response) || ($response['status'] ?? null) === 'error') {
            Log::warning('FarazSMS balance rejected.', [
                'status' => is_array($response) ? ($response['status'] ?? null) : null,
            ]);

            return null;
        }

        $credit = $this->extractCredit($response['data'] ?? null);

        if ($credit === null) {
            Log::warning('FarazSMS balance payload unrecognized.', [
                'data_keys' => is_array($response['data'] ?? null) ? array_keys($response['data']) : null,
            ]);
        }

        return $credit;
    }

    private function extractCredit(mixed $data): ?float
    {
        if (is_numeric($data)) {
            return (float) $data;
        }

        if (! is_array($data)) {
            return null;
        }

        foreach (['balance_amount', 'credit', 'balance', 'amount', 'wallet', 'balance_count', 'sms_count'] as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) {
                return (float) $data[$key];
            }
        }

        return null;
    }

    private function http(string $apiKey): PendingRequest
    {
        $baseUrl = rtrim((string) config('services.farazsms.base_url', 'https://api.iranpayamak.com'), '/');

        $options = [
            'force_ip_resolve' => 'v4',
        ];

        $caBundle = trim((string) config('services.farazsms.ca_bundle', ''));
        if ($caBundle !== '' && is_file($caBundle)) {
            $options['verify'] = $caBundle;
        }

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->withHeaders(['Api-Key' => $apiKey])
            ->withOptions($options)
            ->connectTimeout(5)
            ->timeout(20);
    }
}
