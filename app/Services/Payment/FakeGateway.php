<?php

declare(strict_types=1);

namespace App\Services\Payment;

/**
 * In-process gateway for tests and PAYMENT_GATEWAY=fake. Does not call a live API.
 */
class FakeGateway implements GatewayInterface
{
    public static bool $verifySucceeds = true;

    public function start(string $amount, string $callbackUrl, string $description): array
    {
        $authority = 'A'.str_pad(bin2hex(random_bytes(17)), 35, '0');

        return [
            'authority' => $authority,
            'payment_url' => 'https://gateway.test/pay/'.$authority,
            'request_payload' => [
                'amount' => $amount,
                'callback_url' => $callbackUrl,
                'description' => $description,
            ],
            'response_payload' => [
                'authority' => $authority,
            ],
        ];
    }

    public function verify(string $authority, string $amount): array
    {
        unset($amount);

        if (! self::$verifySucceeds) {
            return [
                'ok' => false,
                'transaction_id' => '',
                'reference_id' => '',
                'response_payload' => ['code' => -1],
            ];
        }

        return [
            'ok' => true,
            'transaction_id' => 'TX-'.$authority,
            'reference_id' => 'REF-'.$authority,
            'response_payload' => ['code' => 100],
        ];
    }
}
