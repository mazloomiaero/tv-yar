<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\ServiceReportCreated;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\Part;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ServiceAssignment;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Models\Technician;
use App\Models\User;
use App\Support\InitialServiceStatus;
use App\Support\PermissionName;
use App\Support\RoleName;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_assigned_technician_can_create_a_report_with_catalog_and_free_parts_and_images(): void
    {
        Event::fake([ServiceReportCreated::class]);
        Storage::fake('public');

        $user = $this->staffUser(RoleName::Technician);
        $context = $this->assignedContext($user);
        $catalog = Part::factory()->create([
            'name' => 'Compressor',
            'default_unit_price' => '2000.00',
        ]);

        $before = $this->pngUpload('before.png');
        $after = $this->pngUpload('after.png');

        $response = $this->actingAs($user)
            ->post(route('technician.reports.store', $context['assignment']), [
                'parts' => [
                    [
                        'part_id' => $catalog->id,
                        'name' => 'Client override name',
                        'quantity' => '2.5',
                        'unit_price' => '2100.00',
                        'total_price' => '1.00',
                    ],
                    [
                        'part_id' => $catalog->id,
                        'quantity' => '1',
                    ],
                    [
                        'name' => 'Free gasket',
                        'quantity' => '3',
                        'unit_price' => '10.50',
                        'total_price' => '999.00',
                    ],
                ],
                'images' => [
                    'before' => [$before],
                    'after' => [$after],
                ],
            ]);

        $report = ServiceReport::query()->first();
        $this->assertNotNull($report);
        $response->assertRedirect(route('technician.reports.show', $report));

        $this->assertSame($context['request']->id, $report->service_request_id);
        $this->assertArrayNotHasKey('diagnosis', $report->getAttributes());
        $this->assertArrayNotHasKey('notes', $report->getAttributes());
        $this->assertSame(3, $report->parts()->count());

        $catalogEdited = $report->parts()->where('unit_price', '2100.00')->first();
        $this->assertNotNull($catalogEdited);
        $this->assertSame($catalog->id, $catalogEdited->part_id);
        $this->assertSame('Compressor', $catalogEdited->name);
        $this->assertSame('2.500', (string) $catalogEdited->quantity);
        $this->assertSame('5250.00', (string) $catalogEdited->total_price);

        $catalogDefault = $report->parts()->where('unit_price', '2000.00')->first();
        $this->assertNotNull($catalogDefault);
        $this->assertSame('2000.00', (string) $catalogDefault->total_price);

        $free = $report->parts()->whereNull('part_id')->first();
        $this->assertNotNull($free);
        $this->assertSame('Free gasket', $free->name);
        $this->assertSame('31.50', (string) $free->total_price);

        $this->assertSame(2, $report->images()->count());
        $beforeImage = $report->images()->where('kind', 'before')->first();
        $this->assertNotNull($beforeImage);
        $this->assertStringStartsWith('service-reports/before/', $beforeImage->path);
        Storage::disk('public')->assertExists($beforeImage->path);

        $afterImage = $report->images()->where('kind', 'after')->first();
        $this->assertNotNull($afterImage);
        $this->assertStringStartsWith('service-reports/after/', $afterImage->path);

        Event::assertDispatched(
            ServiceReportCreated::class,
            fn (ServiceReportCreated $event): bool => $event->serviceReportId === $report->id,
        );

        $this->actingAs($user)
            ->get(route('technician.reports.show', $report))
            ->assertOk()
            ->assertSee('Compressor')
            ->assertSee('Free gasket');
    }

    public function test_unassigned_technician_cannot_create_a_report(): void
    {
        $user = $this->staffUser(RoleName::Technician);
        Technician::factory()->create(['user_id' => $user->id]);
        $other = Technician::factory()->create();
        $assignment = ServiceAssignment::factory()->create(['technician_id' => $other->id]);

        $this->actingAs($user)
            ->post(route('technician.reports.store', $assignment), [
                'parts' => [
                    [
                        'name' => 'Free gasket',
                        'quantity' => '1',
                        'unit_price' => '10.00',
                    ],
                ],
            ])
            ->assertNotFound();

        $this->assertSame(0, ServiceReport::query()->count());
    }

    public function test_technician_without_report_permission_is_forbidden(): void
    {
        $user = $this->staffUser(RoleName::Technician);
        $role = Role::query()->where('name', RoleName::Technician->value)->firstOrFail();
        $role->permissions()->detach(
            Permission::query()->where('name', PermissionName::ReportsCreate->value)->pluck('id')
        );
        $context = $this->assignedContext($user);

        $this->actingAs($user)
            ->post(route('technician.reports.store', $context['assignment']), [
                'parts' => [
                    [
                        'name' => 'Free gasket',
                        'quantity' => '1',
                        'unit_price' => '10.00',
                    ],
                ],
            ])
            ->assertForbidden();
    }

    public function test_free_text_part_requires_name_and_price(): void
    {
        $user = $this->staffUser(RoleName::Technician);
        $context = $this->assignedContext($user);

        $this->actingAs($user)
            ->from(route('technician.reports.create', $context['assignment']))
            ->post(route('technician.reports.store', $context['assignment']), [
                'parts' => [
                    [
                        'quantity' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('technician.reports.create', $context['assignment']))
            ->assertSessionHasErrors(['parts.0.name', 'parts.0.unit_price']);
    }

    public function test_reports_are_not_soft_deleted_entities(): void
    {
        $report = ServiceReport::factory()->create();

        $this->assertDatabaseHas('service_reports', ['id' => $report->id]);
        $this->assertNotContains(
            SoftDeletes::class,
            class_uses_recursive(ServiceReport::class),
        );
    }

    /**
     * @return array{assignment: ServiceAssignment, request: ServiceRequest}
     */
    private function assignedContext(User $user): array
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
        $technician = Technician::factory()->create(['user_id' => $user->id]);
        $assignment = ServiceAssignment::factory()->create([
            'service_request_id' => $request->id,
            'technician_id' => $technician->id,
        ]);

        return [
            'assignment' => $assignment,
            'request' => $request,
        ];
    }

    private function pngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, is_string($png) ? $png : '');
    }
}
