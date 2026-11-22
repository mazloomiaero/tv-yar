<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\FarazSmsCredentialsStore;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmsSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
        Storage::fake('local');
        config()->set('services.farazsms.base_url', 'https://api.iranpayamak.com');
        config()->set('services.farazsms.api_key', '');
        config()->set('services.farazsms.pattern_code', '');
        config()->set('services.farazsms.otp_param', 'code');
        config()->set('services.farazsms.line_number', '90008361');
    }

    public function test_admin_can_update_sms_settings(): void
    {
        Http::fake([
            'api.iranpayamak.com/ws/v1/account/balance' => Http::response([
                'status' => 'success',
                'data' => ['credit' => 42],
            ], 200),
        ]);

        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->put(route('admin.sms-settings.update'), [
                'api_key' => 'faraz-key',
                'pattern_code' => 'SJ3FgPrE0C',
                'otp_param' => 'code',
                'line_number' => '90008361',
            ])
            ->assertRedirect(route('admin.sms-settings.edit'));

        $resolved = app(FarazSmsCredentialsStore::class)->resolved();

        $this->assertSame('faraz-key', $resolved['api_key']);
        $this->assertSame('SJ3FgPrE0C', $resolved['pattern_code']);
        $this->assertSame('code', $resolved['otp_param']);
        $this->assertSame('90008361', $resolved['line_number']);
    }

    public function test_guest_cannot_update_sms_settings(): void
    {
        $this->put(route('admin.sms-settings.update'), [
            'api_key' => 'faraz-key',
            'pattern_code' => 'SJ3FgPrE0C',
            'otp_param' => 'code',
            'line_number' => '90008361',
        ])->assertRedirect();

        $this->assertFalse(Storage::disk('local')->exists(FarazSmsCredentialsStore::PATH));
    }

    public function test_admin_dashboard_shows_sms_credit_when_configured(): void
    {
        Http::fake([
            'api.iranpayamak.com/ws/v1/account/balance' => Http::response([
                'status' => 'success',
                'data' => ['credit' => 1200],
            ], 200),
        ]);

        app(FarazSmsCredentialsStore::class)->update([
            'api_key' => 'faraz-key',
            'pattern_code' => 'SJ3FgPrE0C',
        ]);

        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('اعتبار پیامک')
            ->assertSee('1,200');
    }
}
