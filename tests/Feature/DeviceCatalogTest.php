<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\DeviceType;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_can_create_and_update_device_types(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $create = $this->actingAs($admin)
            ->post(route('admin.device-types.store'), ['name' => 'TV']);

        $type = DeviceType::query()->where('name', 'TV')->first();

        $this->assertNotNull($type);
        $create->assertRedirect(route('admin.device-types.edit', $type));

        $this->actingAs($admin)
            ->get(route('admin.device-types.index'))
            ->assertOk()
            ->assertSee('ویرایش', false)
            ->assertSee('حذف', false);

        $this->actingAs($admin)
            ->put(route('admin.device-types.update', $type), ['name' => 'Air Conditioner'])
            ->assertRedirect(route('admin.device-types.edit', $type));

        $this->assertSame('Air Conditioner', $type->fresh()->name);
    }

    public function test_admin_can_create_and_soft_delete_a_brand(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->post(route('admin.brands.store'), ['name' => 'Generic']);

        $brand = Brand::query()->where('name', 'Generic')->first();

        $this->assertNotNull($brand);

        $this->actingAs($admin)
            ->get(route('admin.brands.index'))
            ->assertOk()
            ->assertSee('ویرایش', false)
            ->assertSee('حذف', false);

        $this->actingAs($admin)
            ->delete(route('admin.brands.destroy', $brand))
            ->assertRedirect(route('admin.brands.index'));

        $this->assertSoftDeleted($brand);
    }

    public function test_admin_catalog_validation_failure(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->from(route('admin.device-types.create'))
            ->post(route('admin.device-types.store'), [])
            ->assertRedirect(route('admin.device-types.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_operator_cannot_open_admin_catalog(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('admin.device-types.index'))
            ->assertForbidden();

        $this->actingAs($operator)
            ->get(route('admin.brands.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_admin_catalog(): void
    {
        $this->get(route('admin.device-types.index'))
            ->assertRedirect(route('login'));
    }
}
