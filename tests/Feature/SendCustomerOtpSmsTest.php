<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Support\CustomerOtpStore;
use App\Support\FarazSmsCredentialsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SendCustomerOtpSmsTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_sending_otp_calls_farazsms_when_configured(): void
    {
        Http::fake([
            'api.iranpayamak.com/ws/v1/sms/pattern' => Http::response(['status' => 'success'], 201),
        ]);

        app(FarazSmsCredentialsStore::class)->update([
            'api_key' => 'faraz-key',
            'pattern_code' => 'SJ3FgPrE0C',
            'otp_param' => 'code',
            'line_number' => '90008361',
        ]);

        $customer = Customer::factory()->create(['mobile' => '09126667777']);

        $this->post(route('customer.login.otp'), [
            'mobile' => $customer->mobile,
        ])->assertRedirect(route('customer.login.verify'));

        $this->assertTrue(Cache::has(app(CustomerOtpStore::class)->key($customer->mobile)));

        Http::assertSent(function ($request) use ($customer): bool {
            $data = $request->data();

            return $request->url() === 'https://api.iranpayamak.com/ws/v1/sms/pattern'
                && ($data['recipient'] ?? null) === $customer->mobile
                && ($data['code'] ?? null) === 'SJ3FgPrE0C'
                && isset($data['attributes']['code'])
                && is_string($data['attributes']['code'])
                && strlen($data['attributes']['code']) === 6;
        });
    }

    public function test_sending_otp_does_not_call_farazsms_when_unconfigured(): void
    {
        Http::fake();

        $customer = Customer::factory()->create(['mobile' => '09128889999']);

        $this->post(route('customer.login.otp'), [
            'mobile' => $customer->mobile,
        ])->assertRedirect(route('customer.login.verify'));

        $this->assertTrue(Cache::has(app(CustomerOtpStore::class)->key($customer->mobile)));
        Http::assertNothingSent();
    }

    public function test_sms_failure_still_keeps_hashed_otp_in_cache(): void
    {
        Http::fake([
            'api.iranpayamak.com/ws/v1/sms/pattern' => Http::response(['status' => 'error', 'message' => 'fail'], 400),
        ]);

        app(FarazSmsCredentialsStore::class)->update([
            'api_key' => 'faraz-key',
            'pattern_code' => 'SJ3FgPrE0C',
        ]);

        $customer = Customer::factory()->create(['mobile' => '09121112222']);

        $this->post(route('customer.login.otp'), [
            'mobile' => $customer->mobile,
        ])->assertRedirect(route('customer.login.verify'));

        $this->assertTrue(Cache::has(app(CustomerOtpStore::class)->key($customer->mobile)));
    }
}
