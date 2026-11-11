<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\ServiceAssignment;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceStatusHistory;
use App\Models\Technician;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_cannot_delete_a_service_status_referenced_by_a_request(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $status = ServiceStatus::factory()->create([
            'name' => 'Custom hold',
            'is_system' => false,
        ]);
        $request = ServiceRequest::factory()->create(['status_id' => $status->id]);
        ServiceStatusHistory::query()->create([
            'service_request_id' => $request->id,
            'service_status_id' => $status->id,
            'changed_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.service-statuses.edit', $status))
            ->delete(route('admin.service-statuses.destroy', $status))
            ->assertRedirect(route('admin.service-statuses.edit', $status))
            ->assertSessionHasErrors([
                'delete' => 'Cannot delete this service status because 2 dependent records still reference it.',
            ]);

        $this->assertNotSoftDeleted($status);
    }

    public function test_admin_cannot_delete_a_system_service_status(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $status = ServiceStatus::factory()->create([
            'name' => 'جدید',
            'code' => 'new',
            'is_system' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.service-statuses.edit', $status))
            ->delete(route('admin.service-statuses.destroy', $status))
            ->assertRedirect(route('admin.service-statuses.edit', $status))
            ->assertSessionHasErrors([
                'delete' => 'Cannot delete this service status because it is a system status.',
            ]);

        $this->assertNotSoftDeleted($status);
    }

    public function test_admin_can_soft_delete_an_unreferenced_device_type(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $type = DeviceType::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.device-types.destroy', $type))
            ->assertRedirect(route('admin.device-types.index'));

        $this->assertSoftDeleted($type);
    }

    public function test_admin_cannot_delete_a_device_type_referenced_by_a_device(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $type = DeviceType::factory()->create();
        Device::factory()->create(['device_type_id' => $type->id]);

        $this->actingAs($admin)
            ->from(route('admin.device-types.edit', $type))
            ->delete(route('admin.device-types.destroy', $type))
            ->assertRedirect(route('admin.device-types.edit', $type))
            ->assertSessionHasErrors([
                'delete' => 'Cannot delete this device type because 1 dependent record still references it.',
            ]);

        $this->assertNotSoftDeleted($type);
    }

    public function test_admin_cannot_delete_a_brand_referenced_by_a_device(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $brand = Brand::factory()->create();
        Device::factory()->create(['brand_id' => $brand->id]);

        $this->actingAs($admin)
            ->from(route('admin.brands.edit', $brand))
            ->delete(route('admin.brands.destroy', $brand))
            ->assertRedirect(route('admin.brands.edit', $brand))
            ->assertSessionHasErrors([
                'delete' => 'Cannot delete this brand because 1 dependent record still references it.',
            ]);

        $this->assertNotSoftDeleted($brand);
    }

    public function test_admin_cannot_delete_a_brand_referenced_by_a_soft_deleted_device(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $brand = Brand::factory()->create();
        $device = Device::factory()->create(['brand_id' => $brand->id]);
        $device->delete();

        $this->actingAs($admin)
            ->from(route('admin.brands.edit', $brand))
            ->delete(route('admin.brands.destroy', $brand))
            ->assertRedirect(route('admin.brands.edit', $brand))
            ->assertSessionHasErrors([
                'delete' => 'Cannot delete this brand because 1 dependent record still references it.',
            ]);

        $this->assertNotSoftDeleted($brand);
    }

    public function test_admin_cannot_delete_a_branch_referenced_by_a_technician_or_assignment(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $branch = Branch::factory()->create();
        $technician = Technician::factory()->create(['branch_id' => $branch->id]);
        ServiceAssignment::factory()->create([
            'branch_id' => $branch->id,
            'technician_id' => $technician->id,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.branches.edit', $branch))
            ->delete(route('admin.branches.destroy', $branch))
            ->assertRedirect(route('admin.branches.edit', $branch))
            ->assertSessionHasErrors([
                'delete' => 'Cannot delete this branch because 2 dependent records still reference it.',
            ]);

        $this->assertNotSoftDeleted($branch);
    }
}
