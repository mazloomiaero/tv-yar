<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionName;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_operator_can_create_a_device_for_an_existing_customer(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create();
        $type = DeviceType::factory()->create(['name' => 'TV']);
        $brand = Brand::factory()->create(['name' => 'Sony']);

        $response = $this->actingAs($operator)
            ->post(route('operator.customers.devices.store', $customer), $this->devicePayload($type, $brand));

        $device = Device::query()->where('serial_number', 'SN-100')->first();

        $this->assertNotNull($device);
        $this->assertSame($customer->id, $device->customer_id);
        $this->assertSame($type->id, $device->device_type_id);
        $this->assertSame($brand->id, $device->brand_id);
        $this->assertSame('Living room TV', $device->title);
        $this->assertFalse(isset($device->imei));

        $response->assertRedirect(route('operator.customers.devices.edit', [$customer, $device]));
    }

    public function test_operator_saves_mapped_device_custom_field_values(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create();
        $type = DeviceType::factory()->create(['name' => 'TV']);
        $brand = Brand::factory()->create();
        $screenSize = CustomField::factory()->create(['name' => 'Screen Size']);
        $panelType = CustomField::factory()->create(['name' => 'Panel Type']);
        $screenSize->deviceTypes()->sync([$type->id]);
        $panelType->deviceTypes()->sync([$type->id]);
        $panelType->options()->create(['name' => 'LED']);
        $panelType->options()->create(['name' => 'OLED']);

        $this->actingAs($operator)
            ->post(route('operator.customers.devices.store', $customer), $this->devicePayload($type, $brand, [
                'custom_field_values' => [
                    $screenSize->id => '55',
                    $panelType->id => 'OLED',
                ],
            ]));

        $device = Device::query()->where('serial_number', 'SN-100')->first();

        $this->assertNotNull($device);
        $this->assertDatabaseHas('device_custom_field_values', [
            'device_id' => $device->id,
            'custom_field_id' => $screenSize->id,
            'value' => '55',
        ]);
        $this->assertDatabaseHas('device_custom_field_values', [
            'device_id' => $device->id,
            'custom_field_id' => $panelType->id,
            'value' => 'OLED',
        ]);
        $this->assertArrayNotHasKey('screen_size', $device->getAttributes());
        $this->assertArrayNotHasKey('panel_type', $device->getAttributes());
    }

    public function test_invalid_select_custom_field_value_is_rejected(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create();
        $type = DeviceType::factory()->create();
        $brand = Brand::factory()->create();
        $gas = CustomField::factory()->create(['name' => 'Gas Type']);
        $gas->deviceTypes()->sync([$type->id]);
        $gas->options()->create(['name' => 'R22']);

        $this->actingAs($operator)
            ->from(route('operator.customers.devices.create', $customer))
            ->post(route('operator.customers.devices.store', $customer), $this->devicePayload($type, $brand, [
                'custom_field_values' => [
                    $gas->id => 'R32',
                ],
            ]))
            ->assertRedirect(route('operator.customers.devices.create', $customer))
            ->assertSessionHasErrors('custom_field_values.'.$gas->id);

        $this->assertSame(0, Device::query()->count());
    }

    public function test_operator_can_update_device_custom_field_values(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create();
        $type = DeviceType::factory()->create();
        $brand = Brand::factory()->create();
        $imei = CustomField::factory()->create(['name' => 'IMEI']);
        $imei->deviceTypes()->sync([$type->id]);
        $device = Device::factory()->for($customer)->create([
            'device_type_id' => $type->id,
            'brand_id' => $brand->id,
            'serial_number' => 'SN-200',
        ]);

        $this->actingAs($operator)
            ->put(
                route('operator.customers.devices.update', [$customer, $device]),
                $this->devicePayload($type, $brand, [
                    'serial_number' => 'SN-200',
                    'custom_field_values' => [
                        $imei->id => '123456789012345',
                    ],
                ]),
            )
            ->assertRedirect(route('operator.customers.devices.edit', [$customer, $device]));

        $this->assertDatabaseHas('device_custom_field_values', [
            'device_id' => $device->id,
            'custom_field_id' => $imei->id,
            'value' => '123456789012345',
        ]);
    }

    public function test_create_does_not_store_invented_device_columns(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create();
        $type = DeviceType::factory()->create();
        $brand = Brand::factory()->create();

        $this->actingAs($operator)
            ->post(route('operator.customers.devices.store', $customer), $this->devicePayload($type, $brand, [
                'imei' => '123456789012345',
                'screen_size' => '55',
                'customer_id' => Customer::factory()->create()->id,
            ]));

        $device = Device::query()->where('serial_number', 'SN-100')->first();

        $this->assertNotNull($device);
        $this->assertSame($customer->id, $device->customer_id);
        $this->assertArrayNotHasKey('imei', $device->getAttributes());
        $this->assertArrayNotHasKey('screen_size', $device->getAttributes());
    }

    public function test_create_validation_failure(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create();

        $this->actingAs($operator)
            ->from(route('operator.customers.devices.create', $customer))
            ->post(route('operator.customers.devices.store', $customer), [])
            ->assertRedirect(route('operator.customers.devices.create', $customer))
            ->assertSessionHasErrors([
                'device_type_id',
                'brand_id',
                'model',
                'serial_number',
                'title',
                'purchase_date',
                'warranty_status',
                'warranty_until',
            ]);
    }

    public function test_soft_deleted_type_cannot_be_selected(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create();
        $type = DeviceType::factory()->create();
        $type->delete();
        $brand = Brand::factory()->create();

        $this->actingAs($operator)
            ->from(route('operator.customers.devices.create', $customer))
            ->post(route('operator.customers.devices.store', $customer), $this->devicePayload($type, $brand))
            ->assertRedirect(route('operator.customers.devices.create', $customer))
            ->assertSessionHasErrors('device_type_id');
    }

    public function test_operator_can_update_a_device(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create();
        $device = Device::factory()->for($customer)->create([
            'title' => 'Old title',
            'serial_number' => 'SN-200',
        ]);
        $type = DeviceType::factory()->create(['name' => 'Mobile']);
        $brand = Brand::factory()->create();

        $this->actingAs($operator)
            ->put(
                route('operator.customers.devices.update', [$customer, $device]),
                $this->devicePayload($type, $brand, [
                    'title' => 'New title',
                    'serial_number' => 'SN-200',
                    'customer_id' => Customer::factory()->create()->id,
                ]),
            )
            ->assertRedirect(route('operator.customers.devices.edit', [$customer, $device]));

        $device->refresh();

        $this->assertSame('New title', $device->title);
        $this->assertSame($customer->id, $device->customer_id);
        $this->assertSame($type->id, $device->device_type_id);
    }

    public function test_device_from_another_customer_is_not_found(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $owner = Customer::factory()->create();
        $other = Customer::factory()->create();
        $device = Device::factory()->for($owner)->create();

        $this->actingAs($operator)
            ->get(route('operator.customers.devices.edit', [$other, $device]))
            ->assertNotFound();
    }

    public function test_operator_without_device_permissions_is_forbidden(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $role = Role::query()->where('name', RoleName::Operator->value)->firstOrFail();
        $role->permissions()->detach(
            Permission::query()->whereIn('name', [
                PermissionName::DevicesView->value,
                PermissionName::DevicesCreate->value,
                PermissionName::DevicesEdit->value,
            ])->pluck('id')
        );

        $customer = Customer::factory()->create();

        $this->actingAs($operator)
            ->get(route('operator.customers.devices.create', $customer))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_device_create(): void
    {
        $customer = Customer::factory()->create();

        $this->get(route('operator.customers.devices.create', $customer))
            ->assertRedirect(route('login'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function devicePayload(DeviceType $type, Brand $brand, array $overrides = []): array
    {
        return array_merge([
            'device_type_id' => $type->id,
            'brand_id' => $brand->id,
            'model' => 'X100',
            'serial_number' => 'SN-100',
            'title' => 'Living room TV',
            'purchase_date' => '2024-01-15',
            'warranty_status' => 'in_warranty',
            'warranty_until' => '2026-01-15',
            'notes' => '',
        ], $overrides);
    }
}
