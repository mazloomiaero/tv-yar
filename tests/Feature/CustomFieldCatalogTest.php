<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CustomField;
use App\Models\DeviceType;
use App\Models\ServiceType;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomFieldCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_can_create_and_update_custom_fields_with_mappings(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $deviceType = DeviceType::factory()->create(['name' => 'TV']);
        $serviceType = ServiceType::factory()->create(['name' => 'Repair']);

        $this->actingAs($admin)
            ->get(route('admin.custom-fields.create'))
            ->assertOk()
            ->assertSee('انواع دستگاه')
            ->assertSee('انواع خدمات')
            ->assertSee('گزینه‌های انتخابی');

        $create = $this->actingAs($admin)
            ->post(route('admin.custom-fields.store'), [
                'name' => 'Panel Type',
                'option_names' => "LED\nOLED",
                'device_type_ids' => [$deviceType->id],
                'service_type_ids' => [$serviceType->id],
            ]);

        $field = CustomField::query()->where('name', 'Panel Type')->first();

        $this->assertNotNull($field);
        $create->assertRedirect(route('admin.custom-fields.edit', $field));
        $this->assertEqualsCanonicalizing(['LED', 'OLED'], $field->options()->pluck('name')->all());
        $this->assertEqualsCanonicalizing([$deviceType->id], $field->deviceTypes()->pluck('device_types.id')->all());
        $this->assertEqualsCanonicalizing([$serviceType->id], $field->serviceTypes()->pluck('service_types.id')->all());
        $this->assertArrayNotHasKey('type', $field->getAttributes());
        $this->assertArrayNotHasKey('required', $field->getAttributes());
        $this->assertArrayNotHasKey('sort_order', $field->getAttributes());

        $this->actingAs($admin)
            ->put(route('admin.custom-fields.update', $field), [
                'name' => 'Screen Size',
                'option_names' => '',
                'device_type_ids' => [$deviceType->id],
            ])
            ->assertRedirect(route('admin.custom-fields.edit', $field));

        $field->refresh();

        $this->assertSame('Screen Size', $field->name);
        $this->assertCount(0, $field->options()->get());
        $this->assertCount(0, $field->serviceTypes()->get());
    }

    public function test_admin_can_soft_delete_a_custom_field(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $field = CustomField::factory()->create(['name' => 'IMEI']);

        $this->actingAs($admin)
            ->delete(route('admin.custom-fields.destroy', $field))
            ->assertRedirect(route('admin.custom-fields.index'));

        $this->assertSoftDeleted($field);
    }

    public function test_admin_catalog_validation_failure(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->from(route('admin.custom-fields.create'))
            ->post(route('admin.custom-fields.store'), [])
            ->assertRedirect(route('admin.custom-fields.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_operator_cannot_open_admin_custom_fields(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('admin.custom-fields.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_admin_custom_fields(): void
    {
        $this->get(route('admin.custom-fields.index'))
            ->assertRedirect(route('login'));
    }
}
