<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\ServiceRequestClosed;
use App\Events\ServiceRequestCreated;
use App\Events\ServiceStatusChanged;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomField;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\CancelledServiceStatus;
use App\Support\InitialServiceStatus;
use App\Support\PermissionName;
use App\Support\RoleName;
use App\Support\ServiceRequestSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_operator_can_create_a_service_request_with_new_status_and_number(): void
    {
        Event::fake([ServiceRequestCreated::class]);

        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();

        $response = $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context),
            );

        $serviceRequest = ServiceRequest::query()->first();

        $this->assertNotNull($serviceRequest);
        $this->assertSame('SR-1405-000001', $serviceRequest->request_number);
        $this->assertSame($operator->id, $serviceRequest->created_by);
        $this->assertSame($context['status']->id, $serviceRequest->status_id);
        $this->assertSame(InitialServiceStatus::NAME, $serviceRequest->status->name);
        $this->assertSame($context['address']->id, $serviceRequest->address_id);
        $this->assertNull($serviceRequest->closed_at);

        $this->assertDatabaseHas('service_status_history', [
            'service_request_id' => $serviceRequest->id,
            'service_status_id' => $context['status']->id,
            'changed_by' => $operator->id,
        ]);

        $response->assertRedirect(route('operator.service-requests.show', $serviceRequest));
        Event::assertDispatched(
            ServiceRequestCreated::class,
            fn (ServiceRequestCreated $event): bool => $event->serviceRequestId === $serviceRequest->id,
        );
    }

    public function test_created_by_cannot_be_taken_from_client_input(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $other = User::factory()->create();
        $context = $this->readyContext();

        $this->actingAs($operator)
            ->from(route('operator.customers.service-requests.create', $context['customer']))
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context, [
                    'created_by' => $other->id,
                    'status_id' => ServiceStatus::factory()->create()->id,
                    'source' => 'api',
                ]),
            )
            ->assertRedirect(route('operator.customers.service-requests.create', $context['customer']))
            ->assertSessionHasErrors('source');

        $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context, [
                    'created_by' => $other->id,
                    'status_id' => ServiceStatus::factory()->create()->id,
                ]),
            );

        $serviceRequest = ServiceRequest::query()->first();

        $this->assertNotNull($serviceRequest);
        $this->assertSame($operator->id, $serviceRequest->created_by);
        $this->assertSame($context['status']->id, $serviceRequest->status_id);
    }

    public function test_operator_can_create_an_address_row_as_the_request_fk(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();

        $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context, [
                    'address_id' => null,
                    'create_address' => '1',
                ]),
            );

        $serviceRequest = ServiceRequest::query()->first();

        $this->assertNotNull($serviceRequest);
        $this->assertNotSame($context['address']->id, $serviceRequest->address_id);
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $serviceRequest->address_id,
            'customer_id' => $context['customer']->id,
        ]);
        $address = CustomerAddress::query()->find($serviceRequest->address_id);
        $this->assertNotNull($address);
        $this->assertArrayNotHasKey('city', $address->getAttributes());
        $this->assertArrayNotHasKey('street', $address->getAttributes());
    }

    public function test_saves_mapped_request_custom_field_values(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $urgency = CustomField::factory()->create(['name' => 'Urgency']);
        $urgency->serviceTypes()->sync([$context['serviceType']->id]);
        $urgency->options()->create(['name' => 'High']);
        $urgency->options()->create(['name' => 'Low']);

        $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context, [
                    'custom_field_values' => [
                        $urgency->id => 'High',
                    ],
                ]),
            );

        $serviceRequest = ServiceRequest::query()->first();

        $this->assertNotNull($serviceRequest);
        $this->assertDatabaseHas('service_request_custom_field_values', [
            'service_request_id' => $serviceRequest->id,
            'custom_field_id' => $urgency->id,
            'value' => 'High',
        ]);
    }

    public function test_device_from_another_customer_is_rejected(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $otherDevice = Device::factory()->create();

        $this->actingAs($operator)
            ->from(route('operator.customers.service-requests.create', $context['customer']))
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context, ['device_id' => $otherDevice->id]),
            )
            ->assertRedirect(route('operator.customers.service-requests.create', $context['customer']))
            ->assertSessionHasErrors('device_id');

        $this->assertSame(0, ServiceRequest::query()->count());
    }

    public function test_service_type_must_be_linked_to_the_device_type(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $otherType = ServiceType::factory()->create(['name' => 'Unrelated']);
        $otherType->statuses()->sync([$context['status']->id]);

        $this->actingAs($operator)
            ->from(route('operator.customers.service-requests.create', $context['customer']))
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context, ['service_type_id' => $otherType->id]),
            )
            ->assertRedirect(route('operator.customers.service-requests.create', $context['customer']))
            ->assertSessionHasErrors('service_type_id');

        $this->assertSame(0, ServiceRequest::query()->count());
    }

    public function test_create_stops_when_new_is_not_mapped_for_the_service_type(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $context['serviceType']->statuses()->sync([]);

        $this->actingAs($operator)
            ->from(route('operator.customers.service-requests.create', $context['customer']))
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context),
            )
            ->assertRedirect(route('operator.customers.service-requests.create', $context['customer']))
            ->assertSessionHasErrors('service_type_id');

        $this->assertSame(0, ServiceRequest::query()->count());
    }

    public function test_create_validation_failure(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create();

        $this->actingAs($operator)
            ->from(route('operator.customers.service-requests.create', $customer))
            ->post(route('operator.customers.service-requests.store', $customer), [])
            ->assertRedirect(route('operator.customers.service-requests.create', $customer))
            ->assertSessionHasErrors([
                'device_id',
                'service_type_id',
                'address_id',
                'source',
                'priority',
                'problem_description',
                'preferred_date',
                'preferred_start_time',
                'preferred_end_time',
            ]);
    }

    public function test_operator_can_change_status_within_service_type_statuses(): void
    {
        Event::fake([ServiceStatusChanged::class]);

        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $assigned = ServiceStatus::factory()->create(['name' => 'Assigned']);
        $context['serviceType']->statuses()->sync([$context['status']->id, $assigned->id]);

        $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context),
            );

        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $this->actingAs($operator)
            ->post(route('operator.service-requests.status', $serviceRequest), [
                'status_id' => $assigned->id,
            ])
            ->assertRedirect(route('operator.service-requests.show', $serviceRequest));

        $serviceRequest->refresh();

        $this->assertSame($assigned->id, $serviceRequest->status_id);
        $this->assertDatabaseHas('service_status_history', [
            'service_request_id' => $serviceRequest->id,
            'service_status_id' => $assigned->id,
            'changed_by' => $operator->id,
        ]);
        Event::assertDispatched(
            ServiceStatusChanged::class,
            fn (ServiceStatusChanged $event): bool => $event->serviceRequestId === $serviceRequest->id,
        );
    }

    public function test_status_outside_service_type_mapping_is_rejected(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $cancelled = ServiceStatus::factory()->create(['name' => 'Cancelled']);

        $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context),
            );

        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $this->actingAs($operator)
            ->from(route('operator.service-requests.show', $serviceRequest))
            ->post(route('operator.service-requests.status', $serviceRequest), [
                'status_id' => $cancelled->id,
            ])
            ->assertRedirect(route('operator.service-requests.show', $serviceRequest))
            ->assertSessionHasErrors('status_id');

        $this->assertSame($context['status']->id, $serviceRequest->fresh()->status_id);
    }

    public function test_operator_can_close_a_request_without_deleting_it(): void
    {
        Event::fake([ServiceRequestClosed::class]);

        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();

        $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context),
            );

        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $this->actingAs($operator)
            ->post(route('operator.service-requests.close', $serviceRequest))
            ->assertRedirect(route('operator.service-requests.show', $serviceRequest));

        $serviceRequest->refresh();

        $this->assertNotNull($serviceRequest->closed_at);
        $this->assertNotNull(ServiceRequest::query()->find($serviceRequest->id));
        Event::assertDispatched(
            ServiceRequestClosed::class,
            fn (ServiceRequestClosed $event): bool => $event->serviceRequestId === $serviceRequest->id,
        );
    }

    public function test_operator_can_cancel_a_request_from_the_list_without_deleting_it(): void
    {
        Event::fake([ServiceStatusChanged::class, ServiceRequestClosed::class]);

        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $cancelled = ServiceStatus::factory()->create([
            'name' => CancelledServiceStatus::NAME,
            'code' => CancelledServiceStatus::CODE,
        ]);
        $context['serviceType']->statuses()->syncWithoutDetaching([$cancelled->id]);

        $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context),
            );

        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $this->actingAs($operator)
            ->post(route('operator.service-requests.cancel', $serviceRequest))
            ->assertRedirect(route('operator.service-requests.index'))
            ->assertSessionHas('status', 'درخواست لغو شد.');

        $serviceRequest->refresh();

        $this->assertSame(CancelledServiceStatus::CODE, $serviceRequest->status?->code);
        $this->assertNotNull($serviceRequest->closed_at);
        $this->assertNotNull(ServiceRequest::query()->find($serviceRequest->id));
        Event::assertDispatched(ServiceStatusChanged::class);
        Event::assertDispatched(ServiceRequestClosed::class);
    }

    public function test_service_request_index_can_filter_created_today(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();

        $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context),
            );

        $todayRequest = ServiceRequest::query()->firstOrFail();
        $oldRequest = ServiceRequest::factory()->create([
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($operator)
            ->get(route('operator.service-requests.index', ['created' => 'today']))
            ->assertOk()
            ->assertSee($todayRequest->request_number)
            ->assertDontSee($oldRequest->request_number)
            ->assertSee('فقط درخواست‌های ثبت‌شده امروز.');
    }

    public function test_closing_an_already_closed_request_fails(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();

        $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context),
            );

        $serviceRequest = ServiceRequest::query()->firstOrFail();
        $this->actingAs($operator)->post(route('operator.service-requests.close', $serviceRequest));

        $this->actingAs($operator)
            ->from(route('operator.service-requests.show', $serviceRequest))
            ->post(route('operator.service-requests.close', $serviceRequest))
            ->assertRedirect(route('operator.service-requests.show', $serviceRequest))
            ->assertSessionHasErrors('service_request');
    }

    public function test_operator_without_request_permissions_is_forbidden(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $role = Role::query()->where('name', RoleName::Operator->value)->firstOrFail();
        $role->permissions()->detach(
            Permission::query()->whereIn('name', [
                PermissionName::RequestsView->value,
                PermissionName::RequestsCreate->value,
                PermissionName::RequestsChangeStatus->value,
                PermissionName::RequestsClose->value,
            ])->pluck('id')
        );

        $customer = Customer::factory()->create();

        $this->actingAs($operator)
            ->get(route('operator.customers.service-requests.create', $customer))
            ->assertForbidden();

        $this->actingAs($operator)
            ->get(route('operator.service-requests.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_request_create(): void
    {
        $customer = Customer::factory()->create();

        $this->get(route('operator.customers.service-requests.create', $customer))
            ->assertRedirect(route('login'));
    }

    public function test_renaming_the_initial_status_display_name_does_not_break_creation(): void
    {
        Event::fake([ServiceRequestCreated::class]);

        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->readyContext();
        $context['status']->name = 'ثبت درخواست';
        $context['status']->save();

        $response = $this->actingAs($operator)
            ->post(
                route('operator.customers.service-requests.store', $context['customer']),
                $this->requestPayload($context),
            );

        $serviceRequest = ServiceRequest::query()->first();

        $this->assertNotNull($serviceRequest);
        $this->assertSame($context['status']->id, $serviceRequest->status_id);
        $this->assertSame('ثبت درخواست', $serviceRequest->status->name);
        $response->assertRedirect(route('operator.service-requests.show', $serviceRequest));
    }

    /**
     * @return array{
     *     customer: Customer,
     *     device: Device,
     *     serviceType: ServiceType,
     *     address: CustomerAddress,
     *     status: ServiceStatus
     * }
     */
    private function readyContext(): array
    {
        $deviceType = DeviceType::factory()->create(['name' => 'TV']);
        $customer = Customer::factory()->create();
        $device = Device::factory()->for($customer)->create([
            'device_type_id' => $deviceType->id,
        ]);
        $status = ServiceStatus::factory()->create([
            'name' => InitialServiceStatus::NAME,
            'code' => InitialServiceStatus::CODE,
        ]);
        $serviceType = ServiceType::factory()->create(['name' => 'Repair']);
        $serviceType->deviceTypes()->sync([$deviceType->id]);
        $serviceType->statuses()->sync([$status->id]);
        $address = CustomerAddress::factory()->for($customer)->create();

        return [
            'customer' => $customer,
            'device' => $device,
            'serviceType' => $serviceType,
            'address' => $address,
            'status' => $status,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function requestPayload(array $context, array $overrides = []): array
    {
        return array_merge([
            'device_id' => $context['device']->id,
            'service_type_id' => $context['serviceType']->id,
            'address_id' => $context['address']->id,
            'source' => ServiceRequestSource::Phone->value,
            'priority' => 'normal',
            'problem_description' => 'No picture',
            'preferred_date' => '2026-09-12',
            'preferred_start_time' => '09:00',
            'preferred_end_time' => '12:00',
            'notes' => '',
        ], $overrides);
    }
}
