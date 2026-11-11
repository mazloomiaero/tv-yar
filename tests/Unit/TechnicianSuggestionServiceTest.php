<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomField;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Models\Skill;
use App\Models\Technician;
use App\Services\Technician\TechnicianSuggestionService;
use App\Support\InitialServiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TechnicianSuggestionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggests_technicians_matching_service_type_or_stored_field_value_ordered_by_open_count(): void
    {
        $serviceType = ServiceType::factory()->create(['name' => 'Repair']);
        $context = $this->requestWithType($serviceType);
        $urgency = CustomField::factory()->create(['name' => 'Urgency']);
        $urgency->serviceTypes()->sync([$serviceType->id]);
        $context['request']->customFieldValues()->create([
            'custom_field_id' => $urgency->id,
            'value' => 'High',
        ]);

        $repairSkill = Skill::factory()->create(['name' => 'Repair']);
        $highSkill = Skill::factory()->create(['name' => 'High']);
        $otherSkill = Skill::factory()->create(['name' => 'Plumbing']);

        $busy = Technician::factory()->create(['name' => 'Busy']);
        $busy->skills()->sync([$repairSkill->id]);
        $light = Technician::factory()->create(['name' => 'Light']);
        $light->skills()->sync([$highSkill->id]);
        $unrelated = Technician::factory()->create(['name' => 'Unrelated']);
        $unrelated->skills()->sync([$otherSkill->id]);

        $otherRequest = ServiceRequest::factory()->create();
        $busy->assignments()->create([
            'service_request_id' => $otherRequest->id,
            'is_suggested' => false,
            'scheduled_date' => '2026-09-12',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 'assigned',
            'technician_response' => 'unspecified',
            'completed_at' => null,
        ]);
        $busy->assignments()->create([
            'service_request_id' => ServiceRequest::factory()->create()->id,
            'is_suggested' => false,
            'scheduled_date' => '2026-09-13',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 'assigned',
            'technician_response' => 'unspecified',
            'completed_at' => now(),
        ]);

        $suggested = $this->app->make(TechnicianSuggestionService::class)
            ->suggest($context['request']->fresh());

        $this->assertSame(['Light', 'Busy'], $suggested->pluck('name')->all());
        $this->assertSame(0, $suggested->firstWhere('name', 'Light')?->open_assignments_count);
        $this->assertSame(1, $suggested->firstWhere('name', 'Busy')?->open_assignments_count);
        $this->assertFalse($suggested->contains(fn (Technician $technician): bool => $technician->id === $unrelated->id));
    }

    public function test_does_not_invent_a_skill_service_type_mapping(): void
    {
        $this->assertFalse(Schema::hasTable('skill_service_type'));
        $this->assertFalse(Schema::hasTable('skill_service_types'));
    }

    /**
     * @return array{request: ServiceRequest}
     */
    private function requestWithType(ServiceType $serviceType): array
    {
        $deviceType = DeviceType::factory()->create();
        $customer = Customer::factory()->create();
        $device = Device::factory()->for($customer)->create(['device_type_id' => $deviceType->id]);
        $status = ServiceStatus::factory()->create(['name' => InitialServiceStatus::NAME]);
        $serviceType->deviceTypes()->sync([$deviceType->id]);
        $serviceType->statuses()->sync([$status->id]);
        $address = CustomerAddress::factory()->for($customer)->create();

        $request = ServiceRequest::factory()->create([
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'service_type_id' => $serviceType->id,
            'address_id' => $address->id,
            'status_id' => $status->id,
        ]);

        return ['request' => $request];
    }
}
