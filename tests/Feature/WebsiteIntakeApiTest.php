<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DeviceType;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Support\InitialServiceStatus;
use App\Support\RoleName;
use App\Support\WebsiteIntakeSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteIntakeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
        Storage::fake('local');
    }

    public function test_intake_creates_customer_device_and_service_request(): void
    {
        $actor = $this->staffUser(RoleName::Operator);
        $context = $this->configureIntake($actor->id);

        $response = $this->postJson(route('intake.website-request'), [
            'full_name' => 'علی رضایی',
            'mobile' => '09121234567',
            'brand' => 'Samsung',
            'problem' => 'تصویر ندارد',
            'address' => 'تهران، خیابان نمونه',
        ], [
            'X-Intake-Key' => $context['api_key'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'درخواست با موفقیت ثبت شد.');

        $this->assertDatabaseHas('customers', [
            'mobile' => '09121234567',
            'first_name' => 'علی',
            'last_name' => 'رضایی',
        ]);

        $request = ServiceRequest::query()->first();
        $this->assertNotNull($request);
        $this->assertSame('website', $request->source);
        $this->assertSame('تصویر ندارد', $request->problem_description);
        $this->assertSame($actor->id, $request->created_by);
        $this->assertStringContainsString('تهران', (string) $request->notes);
    }

    public function test_intake_reuses_existing_customer_by_mobile(): void
    {
        $actor = $this->staffUser(RoleName::Operator);
        $context = $this->configureIntake($actor->id);

        $existing = Customer::factory()->create([
            'mobile' => '09129876543',
            'first_name' => 'قدیمی',
            'last_name' => 'مشتری',
        ]);

        $this->postJson(route('intake.website-request'), [
            'full_name' => 'نام جدید',
            'mobile' => '09129876543',
            'brand' => 'LG',
            'problem' => 'صدا ندارد',
            'address' => 'کرج',
        ], [
            'X-Intake-Key' => $context['api_key'],
        ])->assertCreated();

        $this->assertSame(1, Customer::query()->where('mobile', '09129876543')->count());
        $this->assertDatabaseHas('customers', [
            'id' => $existing->id,
            'first_name' => 'قدیمی',
        ]);
        $this->assertSame(1, ServiceRequest::query()->where('customer_id', $existing->id)->count());
    }

    public function test_intake_rejects_invalid_api_key(): void
    {
        $actor = $this->staffUser(RoleName::Operator);
        $this->configureIntake($actor->id);

        $this->postJson(route('intake.website-request'), [
            'full_name' => 'علی رضایی',
            'mobile' => '09121234567',
            'brand' => 'Samsung',
            'problem' => 'ایراد',
            'address' => 'تهران',
        ], [
            'X-Intake-Key' => 'wrong-key',
        ])->assertUnauthorized();
    }

    public function test_intake_validates_mobile_format(): void
    {
        $actor = $this->staffUser(RoleName::Operator);
        $context = $this->configureIntake($actor->id);

        $this->postJson(route('intake.website-request'), [
            'full_name' => 'علی رضایی',
            'mobile' => '9121234567',
            'brand' => 'Samsung',
            'problem' => 'ایراد',
            'address' => 'تهران',
        ], [
            'X-Intake-Key' => $context['api_key'],
        ])->assertUnprocessable();
    }

    public function test_intake_allows_missing_address(): void
    {
        $actor = $this->staffUser(RoleName::Operator);
        $context = $this->configureIntake($actor->id);

        $this->postJson(route('intake.website-request'), [
            'full_name' => 'سارا محمدی',
            'mobile' => '09123334455',
            'brand' => 'Sony',
            'problem' => 'صفحه سیاه است',
        ], [
            'X-Intake-Key' => $context['api_key'],
        ])->assertCreated();

        $request = ServiceRequest::query()->latest('id')->first();
        $this->assertNotNull($request);
        $this->assertSame('صفحه سیاه است', $request->problem_description);
        $this->assertSame('', trim((string) $request->notes));
    }

    public function test_admin_lists_created_api_keys_with_copy_controls(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $store = app(WebsiteIntakeSettingsStore::class);
        $entry = $store->createKey('سایت اصلی');

        $this->actingAs($admin)
            ->get(route('admin.website-intake.edit'))
            ->assertOk()
            ->assertSee('کلیدهای ساخته‌شده')
            ->assertSee('سایت اصلی')
            ->assertSee($entry['key'])
            ->assertSee('data-copy-target', false)
            ->assertSee('کپی')
            ->assertSee('حذف');
    }

    public function test_admin_can_create_additional_api_key(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->post(route('admin.website-intake.keys.store'), [
                'name' => 'وردپرس دوم',
            ])
            ->assertRedirect(route('admin.website-intake.edit'));

        $keys = app(WebsiteIntakeSettingsStore::class)->resolved()['keys'];
        $this->assertCount(1, $keys);
        $this->assertSame('وردپرس دوم', $keys[0]['name']);
    }

    /**
     * @return array{api_key: string, device_type_id: int, service_type_id: int}
     */
    private function configureIntake(int $actorUserId): array
    {
        $deviceType = DeviceType::factory()->create(['name' => 'TV']);
        $status = ServiceStatus::factory()->create([
            'name' => InitialServiceStatus::NAME,
            'code' => InitialServiceStatus::CODE,
        ]);
        $serviceType = ServiceType::factory()->create(['name' => 'Repair']);
        $serviceType->deviceTypes()->sync([$deviceType->id]);
        $serviceType->statuses()->sync([$status->id]);

        $store = app(WebsiteIntakeSettingsStore::class);
        $entry = $store->createKey('test-wordpress');
        $store->updateDefaults([
            'device_type_id' => $deviceType->id,
            'service_type_id' => $serviceType->id,
            'actor_user_id' => $actorUserId,
        ]);

        return [
            'api_key' => $entry['key'],
            'device_type_id' => $deviceType->id,
            'service_type_id' => $serviceType->id,
        ];
    }
}
