<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\ServiceRequestAssigned;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ServiceAssignment;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Models\Skill;
use App\Models\Technician;
use App\Models\User;
use App\Support\AssignmentInsertDefaults;
use App\Support\InitialServiceStatus;
use App\Support\PermissionName;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_operator_can_assign_a_technician_and_sets_is_suggested_from_the_suggestion_list(): void
    {
        Event::fake([ServiceRequestAssigned::class]);

        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $skill = Skill::factory()->create(['name' => $context['serviceType']->name]);
        $suggested = Technician::factory()->create(['name' => 'Suggested Tech']);
        $suggested->skills()->sync([$skill->id]);
        $other = Technician::factory()->create(['name' => 'Other Tech']);

        $this->actingAs($operator)
            ->get(route('operator.service-requests.show', $context['request']))
            ->assertOk()
            ->assertSee('Suggested Tech')
            ->assertSee('Other Tech');

        $this->actingAs($operator)
            ->post(route('operator.service-requests.assignments.store', $context['request']), [
                'technician_id' => $suggested->id,
                'scheduled_date' => '2026-09-12',
                'start_time' => '09:00',
                'end_time' => '12:00',
                'assigned_by' => User::factory()->create()->id,
                'is_suggested' => '0',
            ])
            ->assertRedirect(route('operator.service-requests.show', $context['request']));

        $assignment = ServiceAssignment::query()->first();

        $this->assertNotNull($assignment);
        $this->assertTrue($assignment->is_suggested);
        $this->assertSame($operator->id, $assignment->assigned_by);
        $this->assertNull($assignment->branch_id);
        $this->assertSame(AssignmentInsertDefaults::STATUS, $assignment->status);
        $this->assertSame(AssignmentInsertDefaults::TECHNICIAN_RESPONSE, $assignment->technician_response);
        $this->assertNull($assignment->accepted_at);
        $this->assertNull($assignment->started_at);
        $this->assertNull($assignment->completed_at);

        Event::assertDispatched(
            ServiceRequestAssigned::class,
            fn (ServiceRequestAssigned $event): bool => $event->serviceRequestId === $context['request']->id,
        );

        $this->actingAs($operator)
            ->post(route('operator.service-requests.assignments.store', $context['request']), [
                'technician_id' => $other->id,
                'scheduled_date' => '2026-09-13',
                'start_time' => '13:00',
                'end_time' => '15:00',
            ]);

        $second = ServiceAssignment::query()->orderByDesc('id')->first();
        $this->assertNotNull($second);
        $this->assertFalse($second->is_suggested);
        $this->assertSame(2, ServiceAssignment::query()->count());
    }

    public function test_technical_supervisor_can_assign_with_optional_branch(): void
    {
        $supervisor = $this->staffUser(RoleName::TechnicalSupervisor);
        $context = $this->readyContext();
        $technician = Technician::factory()->create();
        $branch = Branch::factory()->create(['name' => 'North']);

        $this->actingAs($supervisor)
            ->post(route('technical.service-requests.assignments.store', $context['request']), [
                'technician_id' => $technician->id,
                'branch_id' => $branch->id,
                'scheduled_date' => '2026-09-12',
                'start_time' => '09:00',
                'end_time' => '12:00',
            ])
            ->assertRedirect(route('technical.service-requests.show', $context['request']));

        $this->assertDatabaseHas('service_assignments', [
            'service_request_id' => $context['request']->id,
            'technician_id' => $technician->id,
            'branch_id' => $branch->id,
            'assigned_by' => $supervisor->id,
        ]);
    }

    public function test_assignment_validation_failure(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();

        $this->actingAs($operator)
            ->from(route('operator.service-requests.show', $context['request']))
            ->post(route('operator.service-requests.assignments.store', $context['request']), [])
            ->assertRedirect(route('operator.service-requests.show', $context['request']))
            ->assertSessionHasErrors(['technician_id', 'scheduled_date', 'start_time', 'end_time']);
    }

    public function test_closed_request_cannot_be_assigned(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $context['request']->closed_at = now();
        $context['request']->save();
        $technician = Technician::factory()->create();

        $this->actingAs($operator)
            ->from(route('operator.service-requests.show', $context['request']))
            ->post(route('operator.service-requests.assignments.store', $context['request']), [
                'technician_id' => $technician->id,
                'scheduled_date' => '2026-09-12',
                'start_time' => '09:00',
                'end_time' => '12:00',
            ])
            ->assertRedirect(route('operator.service-requests.show', $context['request']))
            ->assertSessionHasErrors('service_request');
    }

    public function test_operator_without_assign_permission_is_forbidden(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $role = Role::query()->where('name', RoleName::Operator->value)->firstOrFail();
        $role->permissions()->detach(
            Permission::query()->where('name', PermissionName::RequestsAssign->value)->pluck('id')
        );
        $context = $this->readyContext();
        $technician = Technician::factory()->create();

        $this->actingAs($operator)
            ->post(route('operator.service-requests.assignments.store', $context['request']), [
                'technician_id' => $technician->id,
                'scheduled_date' => '2026-09-12',
                'start_time' => '09:00',
                'end_time' => '12:00',
            ])
            ->assertForbidden();
    }

    public function test_calendar_filters_by_scheduled_date(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $match = ServiceAssignment::factory()->create([
            'scheduled_date' => '2026-09-12',
        ]);
        ServiceAssignment::factory()->create([
            'scheduled_date' => '2026-09-13',
        ]);

        $this->actingAs($operator)
            ->get(route('operator.calendar', ['scheduled_date' => '2026-09-12']))
            ->assertOk()
            ->assertSee($match->serviceRequest->request_number)
            ->assertDontSee(ServiceAssignment::query()->where('scheduled_date', '2026-09-13')->first()?->serviceRequest?->request_number);
    }

    public function test_technician_panel_lists_only_linked_user_assignments(): void
    {
        $user = $this->staffUser(RoleName::Technician);
        $mine = Technician::factory()->create(['user_id' => $user->id, 'name' => 'Mine']);
        $theirs = Technician::factory()->create(['name' => 'Theirs']);
        $mineAssignment = ServiceAssignment::factory()->create(['technician_id' => $mine->id]);
        $theirsAssignment = ServiceAssignment::factory()->create(['technician_id' => $theirs->id]);

        $this->actingAs($user)
            ->get(route('technician.assignments.index'))
            ->assertOk()
            ->assertSee($mineAssignment->serviceRequest->request_number)
            ->assertDontSee($theirsAssignment->serviceRequest->request_number);

        $this->actingAs($user)
            ->get(route('technician.assignments.show', $mineAssignment))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('technician.assignments.show', $theirsAssignment))
            ->assertNotFound();
    }

    /**
     * @return array{
     *     request: ServiceRequest,
     *     serviceType: ServiceType
     * }
     */
    private function readyContext(): array
    {
        $deviceType = DeviceType::factory()->create(['name' => 'TV']);
        $customer = Customer::factory()->create();
        $device = Device::factory()->for($customer)->create([
            'device_type_id' => $deviceType->id,
        ]);
        $status = ServiceStatus::factory()->create(['name' => InitialServiceStatus::NAME]);
        $serviceType = ServiceType::factory()->create(['name' => 'Repair']);
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

        return [
            'request' => $request,
            'serviceType' => $serviceType,
        ];
    }
}
