<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\DeviceDelivered;
use App\Events\DeviceReceived;
use App\Events\ServiceStatusChanged;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Device;
use App\Models\DeviceReception;
use App\Models\DeviceType;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\InitialServiceStatus;
use App\Support\ReceptionReceipt;
use App\Support\RoleName;
use App\Support\WorkshopServiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeviceReceptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_technical_supervisor_can_receive_a_device_on_the_same_request(): void
    {
        Event::fake([DeviceReceived::class, ServiceStatusChanged::class]);
        Storage::fake('public');

        $supervisor = $this->staffUser(RoleName::TechnicalSupervisor);
        $context = $this->workshopContext();

        $response = $this->actingAs($supervisor)
            ->post(route('technical.service-requests.receptions.store', $context['request']), [
                'received_date' => '2026-09-11',
                'appearance' => 'Scratched case',
                'accompanying_items' => 'Remote',
                'reason' => 'Workshop repair',
                'notes' => 'Handle with care',
            ]);

        $reception = DeviceReception::query()->first();
        $this->assertNotNull($reception);
        $response->assertRedirect(route('technical.service-requests.show', $context['request']));

        $this->assertSame('REC-1405-000001', $reception->receipt_number);
        $this->assertSame($context['request']->id, $reception->service_request_id);
        $this->assertSame($context['customer']->id, $reception->customer_id);
        $this->assertSame($context['device']->id, $reception->device_id);
        $this->assertSame($supervisor->id, $reception->received_by);
        $this->assertSame('Scratched case', $reception->appearance);
        $this->assertNull($reception->delivery_date);
        $this->assertFalse(Schema::hasColumn('device_receptions', 'shelf'));
        $this->assertFalse(Schema::hasColumn('device_receptions', 'bin'));
        $this->assertFalse(Schema::hasColumn('device_receptions', 'stock_qty'));

        $this->assertSame(
            WorkshopServiceStatus::DeviceReceived,
            $context['request']->fresh()->status->name,
        );
        $this->assertDatabaseHas('service_status_history', [
            'service_request_id' => $context['request']->id,
            'service_status_id' => $context['received']->id,
            'changed_by' => $supervisor->id,
        ]);

        Event::assertDispatched(
            DeviceReceived::class,
            fn (DeviceReceived $event): bool => $event->deviceReceptionId === $reception->id,
        );

        Storage::disk(ReceptionReceipt::DISK)->assertExists(
            ReceptionReceipt::relativePath($reception->receipt_number),
        );
        $this->assertStringStartsWith(
            '%PDF',
            Storage::disk(ReceptionReceipt::DISK)->get(ReceptionReceipt::relativePath($reception->receipt_number)),
        );

        $this->actingAs($supervisor)
            ->get(route('technical.service-requests.show', $context['request']))
            ->assertOk()
            ->assertSee('REC-1405-000001')
            ->assertDontSee('Receive device')
            ->assertSee('تحویل دستگاه');
    }

    public function test_receive_is_rejected_when_device_received_is_not_mapped(): void
    {
        $supervisor = $this->staffUser(RoleName::TechnicalSupervisor);
        $context = $this->workshopContext();
        $context['serviceType']->statuses()->sync([$context['new']->id, $context['delivered']->id]);

        $this->actingAs($supervisor)
            ->from(route('technical.service-requests.show', $context['request']))
            ->post(route('technical.service-requests.receptions.store', $context['request']), [
                'received_date' => '2026-09-11',
            ])
            ->assertRedirect(route('technical.service-requests.show', $context['request']))
            ->assertSessionHasErrors('received_date');

        $this->assertSame(0, DeviceReception::query()->count());
        $this->assertSame($context['new']->id, $context['request']->fresh()->status_id);
    }

    public function test_second_reception_on_the_same_request_is_rejected(): void
    {
        $supervisor = $this->staffUser(RoleName::TechnicalSupervisor);
        $context = $this->workshopContext();
        $this->receiveDevice($supervisor, $context['request']);

        $this->actingAs($supervisor)
            ->from(route('technical.service-requests.show', $context['request']))
            ->post(route('technical.service-requests.receptions.store', $context['request']), [
                'received_date' => '2026-09-12',
            ])
            ->assertRedirect(route('technical.service-requests.show', $context['request']))
            ->assertSessionHasErrors('received_date');

        $this->assertSame(1, DeviceReception::query()->count());
    }

    public function test_technical_supervisor_can_deliver_the_received_device(): void
    {
        Event::fake([DeviceDelivered::class, DeviceReceived::class, ServiceStatusChanged::class]);
        Storage::fake('public');

        $supervisor = $this->staffUser(RoleName::TechnicalSupervisor);
        $context = $this->workshopContext();
        $this->receiveDevice($supervisor, $context['request']);
        $reception = DeviceReception::query()->firstOrFail();

        $this->actingAs($supervisor)
            ->post(route('technical.service-requests.receptions.deliver', $context['request']), [
                'delivery_date' => '2026-09-20',
            ])
            ->assertRedirect(route('technical.service-requests.show', $context['request']));

        $reception->refresh();
        $this->assertSame('2026-09-20', $reception->delivery_date?->format('Y-m-d'));
        $this->assertSame(
            WorkshopServiceStatus::Delivered,
            $context['request']->fresh()->status->name,
        );
        Event::assertDispatched(
            DeviceDelivered::class,
            fn (DeviceDelivered $event): bool => $event->deviceReceptionId === $reception->id,
        );

        $this->assertSame(1, DeviceReception::query()->count());
    }

    public function test_deliver_without_reception_is_rejected(): void
    {
        $supervisor = $this->staffUser(RoleName::TechnicalSupervisor);
        $context = $this->workshopContext();

        $this->actingAs($supervisor)
            ->from(route('technical.service-requests.show', $context['request']))
            ->post(route('technical.service-requests.receptions.deliver', $context['request']), [
                'delivery_date' => '2026-09-20',
            ])
            ->assertRedirect(route('technical.service-requests.show', $context['request']))
            ->assertSessionHasErrors('delivery_date');
    }

    public function test_operator_cannot_use_the_technical_workshop_routes(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->workshopContext();

        $this->actingAs($operator)
            ->post(route('technical.service-requests.receptions.store', $context['request']), [
                'received_date' => '2026-09-11',
            ])
            ->assertForbidden();
    }

    public function test_customer_can_view_own_reception_receipt_only(): void
    {
        Storage::fake('public');

        $supervisor = $this->staffUser(RoleName::TechnicalSupervisor);
        $context = $this->workshopContext();
        $this->receiveDevice($supervisor, $context['request']);

        $this->actingAs($context['customer'], 'customer')
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee($context['request']->request_number)
            ->assertSee('REC-1405-000001');

        $this->actingAs($context['customer'], 'customer')
            ->get(route('customer.service-requests.reception', $context['request']))
            ->assertOk()
            ->assertSee('REC-1405-000001')
            ->assertSee('Scratched case');

        $pdf = $this->actingAs($context['customer'], 'customer')
            ->get(route('customer.service-requests.reception.pdf', $context['request']));
        $pdf->assertOk();
        $pdf->assertHeader('content-type', 'application/pdf');

        $other = Customer::factory()->create();
        $this->actingAs($other, 'customer')
            ->get(route('customer.service-requests.reception', $context['request']))
            ->assertNotFound();
    }

    /**
     * @return array{
     *     request: ServiceRequest,
     *     customer: Customer,
     *     device: Device,
     *     serviceType: ServiceType,
     *     new: ServiceStatus,
     *     received: ServiceStatus,
     *     delivered: ServiceStatus
     * }
     */
    private function workshopContext(): array
    {
        $new = ServiceStatus::factory()->create([
            'name' => InitialServiceStatus::NAME,
            'code' => InitialServiceStatus::CODE,
        ]);
        $received = ServiceStatus::factory()->create([
            'name' => WorkshopServiceStatus::DeviceReceived,
            'code' => WorkshopServiceStatus::DeviceReceivedCode,
        ]);
        $inWorkshop = ServiceStatus::factory()->create([
            'name' => WorkshopServiceStatus::InWorkshop,
            'code' => WorkshopServiceStatus::InWorkshopCode,
        ]);
        $ready = ServiceStatus::factory()->create([
            'name' => WorkshopServiceStatus::ReadyForDelivery,
            'code' => WorkshopServiceStatus::ReadyForDeliveryCode,
        ]);
        $delivered = ServiceStatus::factory()->create([
            'name' => WorkshopServiceStatus::Delivered,
            'code' => WorkshopServiceStatus::DeliveredCode,
        ]);

        $deviceType = DeviceType::factory()->create();
        $serviceType = ServiceType::factory()->create();
        $serviceType->deviceTypes()->sync([$deviceType->id]);
        $serviceType->statuses()->sync([
            $new->id,
            $received->id,
            $inWorkshop->id,
            $ready->id,
            $delivered->id,
        ]);

        $customer = Customer::factory()->create([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
        $device = Device::factory()->create([
            'customer_id' => $customer->id,
            'device_type_id' => $deviceType->id,
            'title' => 'Living room TV',
        ]);
        $address = CustomerAddress::factory()->create(['customer_id' => $customer->id]);
        $request = ServiceRequest::factory()->create([
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'service_type_id' => $serviceType->id,
            'address_id' => $address->id,
            'status_id' => $new->id,
        ]);

        return [
            'request' => $request,
            'customer' => $customer,
            'device' => $device,
            'serviceType' => $serviceType,
            'new' => $new,
            'received' => $received,
            'delivered' => $delivered,
        ];
    }

    private function receiveDevice(User $user, ServiceRequest $serviceRequest): void
    {
        $this->actingAs($user)
            ->post(route('technical.service-requests.receptions.store', $serviceRequest), [
                'received_date' => '2026-09-11',
                'appearance' => 'Scratched case',
                'accompanying_items' => 'Remote',
                'reason' => 'Workshop repair',
                'notes' => 'Handle with care',
            ])
            ->assertRedirect(route('technical.service-requests.show', $serviceRequest));
    }
}
