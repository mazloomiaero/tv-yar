<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Sms\FarazSmsClient;
use App\Support\FarazSmsCredentialsStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class FarazSmsClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config()->set('services.farazsms.base_url', 'https://api.iranpayamak.com');
        config()->set('services.farazsms.api_key', '');
        config()->set('services.farazsms.pattern_code', '');
        config()->set('services.farazsms.otp_param', 'code');
        config()->set('services.farazsms.line_number', '90008361');
    }

    public function test_send_pattern_otp_posts_expected_payload(): void
    {
        Http::fake([
            'api.iranpayamak.com/ws/v1/sms/pattern' => Http::response(['status' => 'success', 'message' => 'ok'], 201),
        ]);

        $store = new FarazSmsCredentialsStore;
        $store->update([
            'api_key' => 'test-api-key',
            'pattern_code' => 'SJ3FgPrE0C',
            'otp_param' => 'code',
            'line_number' => '90008361',
        ]);

        (new FarazSmsClient($store))->sendPatternOtp('09120000000', '654321');

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->url() === 'https://api.iranpayamak.com/ws/v1/sms/pattern'
                && $request->hasHeader('Api-Key', 'test-api-key')
                && ($data['code'] ?? null) === 'SJ3FgPrE0C'
                && ($data['recipient'] ?? null) === '09120000000'
                && ($data['attributes']['code'] ?? null) === '654321'
                && ($data['line_number'] ?? null) === '90008361'
                && ($data['number_format'] ?? null) === 'english';
        });
    }

    public function test_send_pattern_otp_throws_when_not_configured(): void
    {
        Http::fake();

        try {
            (new FarazSmsClient(new FarazSmsCredentialsStore))->sendPatternOtp('09120000000', '123456');
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException) {
            Http::assertNothingSent();
        }
    }

    public function test_credit_returns_numeric_balance_from_object(): void
    {
        Http::fake([
            'api.iranpayamak.com/ws/v1/account/balance' => Http::response([
                'status' => 'success',
                'data' => [
                    'balance_amount' => '50000',
                    'balance_count' => '225.22',
                ],
                'message' => 'ok',
            ], 200),
        ]);

        $store = new FarazSmsCredentialsStore;
        $store->update(['api_key' => 'test-api-key']);

        $this->assertSame(50000.0, (new FarazSmsClient($store))->credit());
    }

    public function test_credit_returns_null_without_api_key(): void
    {
        Http::fake();

        $this->assertNull((new FarazSmsClient(new FarazSmsCredentialsStore))->credit());
        Http::assertNothingSent();
    }

    public function test_credit_returns_null_on_http_failure(): void
    {
        Http::fake([
            'api.iranpayamak.com/ws/v1/account/balance' => Http::response(['status' => 'error'], 500),
        ]);

        $store = new FarazSmsCredentialsStore;
        $store->update(['api_key' => 'test-api-key']);

        $this->assertNull((new FarazSmsClient($store))->credit());
    }
}
