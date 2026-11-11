<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Device;
use App\Models\DeviceCustomFieldValue;
use App\Models\DeviceType;
use App\Models\ServiceRequestCustomFieldValue;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Services\CustomField\CustomFieldService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class CustomFieldServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_fields_for_device_type_only_include_mapped_fields(): void
    {
        $tv = DeviceType::factory()->create(['name' => 'TV']);
        $mobile = DeviceType::factory()->create(['name' => 'Mobile']);
        $screenSize = CustomField::factory()->create(['name' => 'Screen Size']);
        $imei = CustomField::factory()->create(['name' => 'IMEI']);
        $screenSize->deviceTypes()->sync([$tv->id]);
        $imei->deviceTypes()->sync([$mobile->id]);

        $service = $this->app->make(CustomFieldService::class);

        $this->assertEqualsCanonicalizing(
            ['Screen Size'],
            $service->fieldsForDeviceType($tv->id)->pluck('name')->all(),
        );
        $this->assertFalse($service->fieldsForDeviceType($tv->id)->first()->isSelect());
    }

    public function test_select_is_inferred_from_options(): void
    {
        $ac = DeviceType::factory()->create(['name' => 'Air Conditioner']);
        $gas = CustomField::factory()->create(['name' => 'Gas Type']);
        $gas->deviceTypes()->sync([$ac->id]);
        $gas->options()->create(['name' => 'R22']);
        $gas->options()->create(['name' => 'R410A']);

        $field = $this->app->make(CustomFieldService::class)->fieldsForDeviceType($ac->id)->first();

        $this->assertTrue($field->isSelect());
        $this->assertEqualsCanonicalizing(['R22', 'R410A'], $field->options->pluck('name')->all());
    }

    public function test_it_saves_and_loads_device_values_for_mapped_fields_only(): void
    {
        $tv = DeviceType::factory()->create();
        $otherType = DeviceType::factory()->create();
        $screenSize = CustomField::factory()->create(['name' => 'Screen Size']);
        $unmapped = CustomField::factory()->create(['name' => 'IMEI']);
        $screenSize->deviceTypes()->sync([$tv->id]);
        $unmapped->deviceTypes()->sync([$otherType->id]);

        $device = Device::factory()->create(['device_type_id' => $tv->id]);
        $service = $this->app->make(CustomFieldService::class);

        $service->saveDeviceValues($device, [
            $screenSize->id => '55',
            $unmapped->id => '123456789012345',
        ]);

        $this->assertSame([$screenSize->id => '55'], $service->valuesForDevice($device));
        $this->assertDatabaseHas('device_custom_field_values', [
            'device_id' => $device->id,
            'custom_field_id' => $screenSize->id,
            'value' => '55',
        ]);
        $this->assertDatabaseMissing('device_custom_field_values', [
            'device_id' => $device->id,
            'custom_field_id' => $unmapped->id,
        ]);
        $this->assertArrayNotHasKey('imei', $device->fresh()->getAttributes());
        $this->assertArrayNotHasKey('screen_size', $device->fresh()->getAttributes());
    }

    public function test_it_rejects_select_values_that_are_not_options(): void
    {
        $ac = DeviceType::factory()->create();
        $gas = CustomField::factory()->create(['name' => 'Gas Type']);
        $gas->deviceTypes()->sync([$ac->id]);
        $gas->options()->create(['name' => 'R22']);

        $device = Device::factory()->create(['device_type_id' => $ac->id]);

        $this->expectException(InvalidArgumentException::class);

        $this->app->make(CustomFieldService::class)->saveDeviceValues($device, [
            $gas->id => 'Unknown',
        ]);
    }

    public function test_it_clears_values_when_device_type_has_no_mapped_fields(): void
    {
        $tv = DeviceType::factory()->create();
        $plain = DeviceType::factory()->create();
        $field = CustomField::factory()->create(['name' => 'Screen Size']);
        $field->deviceTypes()->sync([$tv->id]);

        $device = Device::factory()->create(['device_type_id' => $tv->id]);
        $service = $this->app->make(CustomFieldService::class);
        $service->saveDeviceValues($device, [$field->id => '55']);

        $device->device_type_id = $plain->id;
        $device->save();
        $service->saveDeviceValues($device, [$field->id => '55']);

        $this->assertSame([], $service->valuesForDevice($device));
        $this->assertSame(0, DeviceCustomFieldValue::query()->where('device_id', $device->id)->count());
    }

    public function test_it_saves_service_request_values_for_mapped_service_type_fields(): void
    {
        $serviceType = ServiceType::factory()->create();
        $field = CustomField::factory()->create(['name' => 'Visit Window']);
        $field->serviceTypes()->sync([$serviceType->id]);

        $requestId = $this->insertServiceRequestRow($serviceType->id);
        $service = $this->app->make(CustomFieldService::class);

        $service->saveServiceRequestValues($requestId, $serviceType->id, [
            $field->id => 'Morning',
        ]);

        $this->assertSame([$field->id => 'Morning'], $service->valuesForServiceRequest($requestId));
        $this->assertDatabaseHas('service_request_custom_field_values', [
            'service_request_id' => $requestId,
            'custom_field_id' => $field->id,
            'value' => 'Morning',
        ]);
        $this->assertSame(1, ServiceRequestCustomFieldValue::query()->count());
    }

    public function test_save_definition_syncs_options_and_mappings(): void
    {
        $type = DeviceType::factory()->create();
        $serviceType = ServiceType::factory()->create();
        $service = $this->app->make(CustomFieldService::class);

        $field = $service->saveDefinition(
            null,
            'Panel Type',
            ['LED', 'OLED', 'LED'],
            [$type->id],
            [$serviceType->id],
        );

        $this->assertSame('Panel Type', $field->name);
        $this->assertEqualsCanonicalizing(['LED', 'OLED'], $field->options->pluck('name')->all());
        $this->assertTrue($field->isSelect());
        $this->assertEqualsCanonicalizing([$type->id], $field->deviceTypes->modelKeys());
        $this->assertEqualsCanonicalizing([$serviceType->id], $field->serviceTypes->modelKeys());
    }

    private function insertServiceRequestRow(int $serviceTypeId): int
    {
        $customer = Customer::factory()->create();
        $device = Device::factory()->for($customer)->create();
        $status = ServiceStatus::factory()->create();
        $addressId = (int) DB::table('customer_addresses')->insertGetId([
            'customer_id' => $customer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('service_requests')->insertGetId([
            'request_number' => 'SR-1405-000001',
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'service_type_id' => $serviceTypeId,
            'address_id' => $addressId,
            'status_id' => $status->id,
            'source' => 'phone',
            'priority' => 'normal',
            'problem_description' => 'Fixture only',
            'preferred_date' => '2026-01-01',
            'preferred_start_time' => '09:00:00',
            'preferred_end_time' => '10:00:00',
            'notes' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
