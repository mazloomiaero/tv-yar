<?php

declare(strict_types=1);

namespace App\Services\Payment;

interface GatewayInterface
{
    /**
     * Start a gateway payment. Must not persist merchant secrets.
     *
     * @return array{
     *     authority: string,
     *     payment_url: string,
     *     request_payload: array<string, mixed>,
     *     response_payload: array<string, mixed>
     * }
     */
    public function start(string $amount, string $callbackUrl, string $description): array;

    /**
     * Real Verify. A user redirect is not success.
     *
     * @return array{
     *     ok: bool,
     *     transaction_id: string,
     *     reference_id: string,
     *     response_payload: array<string, mixed>
     * }
     */
    public function verify(string $authority, string $amount): array;
}
