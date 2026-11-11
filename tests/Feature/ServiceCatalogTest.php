<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DeviceType;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Support\RoleName;
use App\Support\SampleServiceStatus;
use Database\Seeders\ServiceStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_can_create_and_update_service_types_with_mappings(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $deviceType = DeviceType::factory()->create(['name' => 'TV']);
        $status = ServiceStatus::factory()->create(['name' => 'New']);
        $otherStatus = ServiceStatus::factory()->create(['name' => 'Assigned']);

        $this->actingAs($admin)
            ->get(route('admin.service-types.create'))
            ->assertOk()
            ->assertSee('انواع دستگاه')
            ->assertSee('وضعیت‌های مجاز');

        $create = $this->actingAs($admin)
            ->post(route('admin.service-types.store'), [
                'name' => 'Installation',
                'device_type_ids' => [$deviceType->id],
                'service_status_ids' => [$status->id],
            ]);

        $serviceType = ServiceType::query()->where('name', 'Installation')->first();

        $this->assertNotNull($serviceType);
        $create->assertRedirect(route('admin.service-types.edit', $serviceType));
        $this->assertEqualsCanonicalizing([$deviceType->id], $serviceType->deviceTypes()->pluck('device_types.id')->all());
        $this->assertEqualsCanonicalizing([$status->id], $serviceType->statuses()->pluck('service_statuses.id')->all());

        $this->actingAs($admin)
            ->put(route('admin.service-types.update', $serviceType), [
                'name' => 'Repair',
                'device_type_ids' => [$deviceType->id],
                'service_status_ids' => [$otherStatus->id],
            ])
            ->assertRedirect(route('admin.service-types.edit', $serviceType));

        $serviceType->refresh();

        $this->assertSame('Repair', $serviceType->name);
        $this->assertEqualsCanonicalizing([$otherStatus->id], $serviceType->statuses()->pluck('service_statuses.id')->all());
    }

    public function test_admin_can_clear_service_type_mappings(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $deviceType = DeviceType::factory()->create();
        $status = ServiceStatus::factory()->create();
        $serviceType = ServiceType::factory()->create(['name' => 'Visit']);
        $serviceType->deviceTypes()->sync([$deviceType->id]);
        $serviceType->statuses()->sync([$status->id]);

        $this->actingAs($admin)
            ->put(route('admin.service-types.update', $serviceType), [
                'name' => 'Visit',
            ])
            ->assertRedirect(route('admin.service-types.edit', $serviceType));

        $this->assertCount(0, $serviceType->deviceTypes()->get());
        $this->assertCount(0, $serviceType->statuses()->get());
    }

    public function test_admin_can_create_and_soft_delete_a_service_status(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->post(route('admin.service-statuses.store'), ['name' => 'New']);

        $status = ServiceStatus::query()->where('name', 'New')->first();

        $this->assertNotNull($status);

        $this->actingAs($admin)
            ->delete(route('admin.service-statuses.destroy', $status))
            ->assertRedirect(route('admin.service-statuses.index'));

        $this->assertSoftDeleted($status);
    }

    public function test_admin_catalog_validation_failure(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->from(route('admin.service-types.create'))
            ->post(route('admin.service-types.store'), [])
            ->assertRedirect(route('admin.service-types.create'))
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)
            ->from(route('admin.service-statuses.create'))
            ->post(route('admin.service-statuses.store'), [])
            ->assertRedirect(route('admin.service-statuses.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_invalid_mapping_ids_are_rejected(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->from(route('admin.service-types.create'))
            ->post(route('admin.service-types.store'), [
                'name' => 'Installation',
                'device_type_ids' => [999],
            ])
            ->assertRedirect(route('admin.service-types.create'))
            ->assertSessionHasErrors('device_type_ids.0');
    }

    public function test_operator_cannot_open_admin_service_catalog(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('admin.service-types.index'))
            ->assertForbidden();

        $this->actingAs($operator)
            ->get(route('admin.service-statuses.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_admin_service_catalog(): void
    {
        $this->get(route('admin.service-types.index'))
            ->assertRedirect(route('login'));
    }

    public function test_sample_statuses_seeder_uses_documented_names_only(): void
    {
        $this->seed(ServiceStatusSeeder::class);

        $names = ServiceStatus::query()->orderBy('id')->pluck('name')->all();

        $this->assertSame(SampleServiceStatus::names(), $names);
        $this->assertContains('دستگاه دریافت شد', $names);
        $this->assertContains('در کارگاه', $names);
        $this->assertContains('آماده تحویل', $names);
        $this->assertContains('تحویل شده', $names);
    }
}
