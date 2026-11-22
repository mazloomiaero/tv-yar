<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Support\CustomerOtpStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerOtpLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_verify_otp_and_reach_the_customer_panel(): void
    {
        $customer = Customer::factory()->create(['mobile' => '09120000000']);

        $this->putOtp($customer->mobile, '123456');

        $this->withSession(['customer_otp_mobile' => $customer->mobile])
            ->post(route('customer.login.verify.store'), ['otp' => '123456'])
            ->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticatedAs($customer, 'customer');
        $this->get(route('customer.dashboard'))->assertOk();
    }

    public function test_sending_otp_for_an_unknown_mobile_does_not_store_a_code(): void
    {
        $this->post(route('customer.login.otp'), [
            'mobile' => '09129999999',
        ])
            ->assertRedirect(route('customer.login.otp.form'))
            ->assertSessionHasErrors(['mobile' => 'این شماره ثبت نمی‌باشد.']);

        $this->assertFalse(Cache::has(app(CustomerOtpStore::class)->key('09129999999')));
    }

    public function test_soft_deleted_customer_with_same_mobile_does_not_block_otp(): void
    {
        $deleted = Customer::factory()->create(['mobile' => '09121111111']);
        $deleted->delete();

        $active = Customer::factory()->create(['mobile' => '09121111111']);

        $this->post(route('customer.login.otp'), [
            'mobile' => $active->mobile,
        ])->assertRedirect(route('customer.login.verify'));

        $this->assertTrue(Cache::has(app(CustomerOtpStore::class)->key($active->mobile)));
    }

    public function test_sending_otp_for_a_unique_mobile_stores_a_hashed_code(): void
    {
        $customer = Customer::factory()->create(['mobile' => '09123333333']);

        $this->post(route('customer.login.otp'), [
            'mobile' => $customer->mobile,
        ])->assertRedirect(route('customer.login.verify'));

        $payload = Cache::get(app(CustomerOtpStore::class)->key($customer->mobile));

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('hash', $payload);
        $this->assertNotSame('123456', $payload['hash']);
    }

    public function test_invalid_otp_is_rejected(): void
    {
        $customer = Customer::factory()->create(['mobile' => '09124444444']);
        $this->putOtp($customer->mobile, '123456');

        $this->withSession(['customer_otp_mobile' => $customer->mobile])
            ->from(route('customer.login.verify'))
            ->post(route('customer.login.verify.store'), ['otp' => '000000'])
            ->assertRedirect(route('customer.login.verify'))
            ->assertSessionHasErrors('otp');

        $this->assertGuest('customer');
    }

    public function test_customer_can_log_out(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer, 'customer')
            ->post(route('customer.logout'))
            ->assertRedirect(route('customer.login'));

        $this->assertGuest('customer');
    }

    public function test_customer_otp_send_is_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('customer.login.otp'), [
                'mobile' => '09125555555',
            ])->assertRedirect(route('customer.login.otp.form'));
        }

        $this->post(route('customer.login.otp'), [
            'mobile' => '09125555555',
        ])->assertStatus(429);
    }

    public function test_sending_otp_for_known_mobile_shows_sent_status(): void
    {
        $customer = Customer::factory()->create(['mobile' => '09126667777']);

        $this->post(route('customer.login.otp'), [
            'mobile' => $customer->mobile,
        ])
            ->assertRedirect(route('customer.login.verify'))
            ->assertSessionHas('status', 'کد ورود ارسال شد.');
    }

    private function putOtp(string $mobile, string $plain): void
    {
        Cache::put(app(CustomerOtpStore::class)->key($mobile), [
            'hash' => Hash::make($plain),
            'attempts' => 0,
        ], CustomerOtpStore::TTL_SECONDS);
    }
}
