<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Part;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_can_create_update_and_soft_delete_a_part(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $create = $this->actingAs($admin)
            ->post(route('admin.parts.store'), [
                'name' => 'Fan motor',
                'code' => 'FM-1',
                'default_unit_price' => '1500.50',
                'unit' => 'pcs',
                'status' => 'active',
            ]);

        $part = Part::query()->where('name', 'Fan motor')->first();

        $this->assertNotNull($part);
        $create->assertRedirect(route('admin.parts.edit', $part));
        $this->assertSame('1500.50', (string) $part->default_unit_price);
        $this->assertArrayNotHasKey('stock_qty', $part->getAttributes());
        $this->assertArrayNotHasKey('warehouse_id', $part->getAttributes());

        $this->actingAs($admin)
            ->put(route('admin.parts.update', $part), [
                'name' => 'Fan motor v2',
                'code' => 'FM-2',
                'default_unit_price' => '1600.00',
                'unit' => 'pcs',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.parts.edit', $part));

        $this->assertSame('Fan motor v2', $part->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.parts.destroy', $part))
            ->assertRedirect(route('admin.parts.index'));

        $this->assertSoftDeleted($part);
    }

    public function test_admin_catalog_validation_failure(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->from(route('admin.parts.create'))
            ->post(route('admin.parts.store'), [])
            ->assertRedirect(route('admin.parts.create'))
            ->assertSessionHasErrors(['name', 'default_unit_price', 'unit', 'status']);
    }

    public function test_operator_cannot_open_admin_parts_catalog(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('admin.parts.index'))
            ->assertForbidden();
    }
}
