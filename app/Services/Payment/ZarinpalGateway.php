<?php

declare(strict_types=1);

namespace App\Services\Payment;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class ZarinpalGateway implements GatewayInterface
{
    public function start(string $amount, string $callbackUrl, string $description): array
    {
        $merchantId = $this->merchantId();
        $payload = [
            'merchant_id' => $merchantId,
            'amount' => $this->integerAmount($amount),
            'callback_url' => $callbackUrl,
            'description' => $description,
        ];

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post($this->requestUrl(), $payload)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            Log::error('Zarinpal request HTTP failed.', [
                'http_status' => $exception->response?->status(),
            ]);

            throw new RuntimeException('Zarinpal request failed.', 0, $exception);
        }

        if (! is_array($response)) {
            throw new RuntimeException('Zarinpal request returned an invalid response.');
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $authority = (string) ($data['authority'] ?? '');

        if ($authority === '') {
            throw new RuntimeException('Zarinpal request did not return an authority.');
        }

        return [
            'authority' => $authority,
            'payment_url' => $this->startPayUrl($authority),
            'request_payload' => [
                'amount' => $payload['amount'],
                'callback_url' => $callbackUrl,
                'description' => $description,
            ],
            'response_payload' => [
                'code' => $data['code'] ?? null,
                'authority' => $authority,
                'message' => $data['message'] ?? null,
            ],
        ];
    }

    public function verify(string $authority, string $amount): array
    {
        $payload = [
            'merchant_id' => $this->merchantId(),
            'amount' => $this->integerAmount($amount),
            'authority' => $authority,
        ];

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post($this->verifyUrl(), $payload)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            Log::error('Zarinpal verify HTTP failed.', [
                'http_status' => $exception->response?->status(),
            ]);

            throw new RuntimeException('Zarinpal verify failed.', 0, $exception);
        }

        if (! is_array($response)) {
            throw new RuntimeException('Zarinpal verify returned an invalid response.');
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $code = (int) ($data['code'] ?? 0);
        $ok = $code === 100 || $code === 101;
        $refId = (string) ($data['ref_id'] ?? $data['refId'] ?? '');

        return [
            'ok' => $ok,
            'transaction_id' => $refId,
            'reference_id' => $refId,
            'response_payload' => [
                'code' => $code,
                'ref_id' => $refId,
                'message' => $data['message'] ?? null,
            ],
        ];
    }

    private function merchantId(): string
    {
        $merchantId = (string) config('services.zarinpal.merchant_id', '');

        if ($merchantId === '') {
            throw new InvalidArgumentException('Zarinpal merchant id is not configured.');
        }

        return $merchantId;
    }

    /**
     * Source does not define IRR conversion. Integer amount is the invoice
     * DECIMAL rounded to a whole unit (gap).
     */
    private function integerAmount(string $amount): int
    {
        $value = (int) round((float) $amount);

        if ($value < 1) {
            throw new InvalidArgumentException('Gateway amount must be at least 1.');
        }

        return $value;
    }

    private function sandbox(): bool
    {
        return (bool) config('services.zarinpal.sandbox', true);
    }

    private function requestUrl(): string
    {
        return $this->sandbox()
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
            : 'https://api.zarinpal.com/pg/v4/payment/request.json';
    }

    private function verifyUrl(): string
    {
        return $this->sandbox()
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
            : 'https://api.zarinpal.com/pg/v4/payment/verify.json';
    }

    private function startPayUrl(string $authority): string
    {
        $base = $this->sandbox()
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://www.zarinpal.com/pg/StartPay/';

        return $base.$authority;
    }
}
