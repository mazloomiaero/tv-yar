<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\RoleName;
use App\Support\StaffOtpStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffOtpLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
        Storage::fake('local');
        config()->set('services.farazsms.api_key', '');
        config()->set('services.farazsms.pattern_code', '');
    }

    public function test_staff_can_verify_otp_and_reach_admin_panel(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin, [
            'mobile' => '09120001111',
        ]);

        $this->putOtp($admin->mobile, '123456');

        $this->withSession(['staff_otp_mobile' => $admin->mobile])
            ->post(route('login.verify.store'), ['otp' => '123456'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_sending_otp_for_unknown_mobile_does_not_store_a_code(): void
    {
        $this->post(route('login.otp'), [
            'mobile' => '09129999999',
        ])
            ->assertRedirect(route('login.otp.form'))
            ->assertSessionHasErrors(['mobile' => 'این شماره ثبت نمی‌باشد.']);

        $this->assertFalse(Cache::has(app(StaffOtpStore::class)->key('09129999999')));
    }

    public function test_sending_otp_for_unique_staff_mobile_stores_a_hashed_code(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin, [
            'mobile' => '09123334444',
        ]);

        $this->post(route('login.otp'), [
            'mobile' => $admin->mobile,
        ])->assertRedirect(route('login.verify'));

        $payload = Cache::get(app(StaffOtpStore::class)->key($admin->mobile));

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('hash', $payload);
    }

    public function test_invalid_staff_otp_is_rejected(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin, [
            'mobile' => '09124445555',
        ]);
        $this->putOtp($admin->mobile, '123456');

        $this->withSession(['staff_otp_mobile' => $admin->mobile])
            ->from(route('login.verify'))
            ->post(route('login.verify.store'), ['otp' => '000000'])
            ->assertRedirect(route('login.verify'))
            ->assertSessionHasErrors('otp');

        $this->assertGuest();
    }

    public function test_staff_otp_send_is_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.otp'), [
                'mobile' => '09125556666',
            ])->assertRedirect(route('login.otp.form'));
        }

        $this->post(route('login.otp'), [
            'mobile' => '09125556666',
        ])->assertStatus(429);
    }

    public function test_sending_otp_for_known_staff_mobile_shows_sent_status(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin, [
            'mobile' => '09127778888',
        ]);

        $this->post(route('login.otp'), [
            'mobile' => $admin->mobile,
        ])
            ->assertRedirect(route('login.verify'))
            ->assertSessionHas('status', 'کد ورود ارسال شد.');
    }

    public function test_staff_login_forms_show_otp_and_password_tabs(): void
    {
        Http::fake();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('رمز یک‌بارمصرف')
            ->assertSee(route('login.otp.form'), false)
            ->assertDontSee('مشتری هستید؟');

        $this->get(route('login.otp.form'))
            ->assertOk()
            ->assertSee('ارسال کد ورود')
            ->assertSee('نام کاربری و رمز');
    }

    private function putOtp(string $mobile, string $plain): void
    {
        Cache::put(app(StaffOtpStore::class)->key($mobile), [
            'hash' => Hash::make($plain),
            'attempts' => 0,
        ], StaffOtpStore::TTL_SECONDS);
    }
}
