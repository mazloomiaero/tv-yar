<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Payment\ZarinpalGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZarinpalGatewayTest extends TestCase
{
    public function test_start_does_not_call_live_api_when_http_is_faked(): void
    {
        Http::fake([
            'sandbox.zarinpal.com/*' => Http::response([
                'data' => [
                    'code' => 100,
                    'authority' => 'A'.str_repeat('1', 35),
                    'message' => 'ok',
                ],
            ], 200),
        ]);

        config()->set('services.zarinpal.merchant_id', 'test-merchant');
        config()->set('services.zarinpal.sandbox', true);

        $started = (new ZarinpalGateway)->start('100.00', 'https://example.test/callback', 'Invoice INV-1');

        $this->assertSame('A'.str_repeat('1', 35), $started['authority']);
        $this->assertArrayNotHasKey('merchant_id', $started['request_payload']);

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->url() === 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
                && ($data['merchant_id'] ?? null) === 'test-merchant'
                && ($data['amount'] ?? null) === 100;
        });
    }
}
