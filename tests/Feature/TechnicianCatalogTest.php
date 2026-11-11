<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Skill;
use App\Models\Technician;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_can_create_and_soft_delete_a_skill(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->post(route('admin.skills.store'), ['name' => 'Repair']);

        $skill = Skill::query()->where('name', 'Repair')->first();

        $this->assertNotNull($skill);

        $this->actingAs($admin)
            ->get(route('admin.skills.index'))
            ->assertOk()
            ->assertSee('ویرایش', false)
            ->assertSee('حذف', false);

        $this->actingAs($admin)
            ->delete(route('admin.skills.destroy', $skill))
            ->assertRedirect(route('admin.skills.index'));

        $this->assertSoftDeleted($skill);
    }

    public function test_admin_can_create_and_update_a_branch_with_documented_columns_only(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $create = $this->actingAs($admin)
            ->post(route('admin.branches.store'), [
                'name' => 'North',
                'province' => 'Tehran',
                'city' => 'Tehran',
                'address' => 'Street 1',
                'phone' => '02100000000',
                'is_default' => '1',
                'status' => 'active',
            ]);

        $branch = Branch::query()->where('name', 'North')->first();

        $this->assertNotNull($branch);
        $create->assertRedirect(route('admin.branches.edit', $branch));

        $this->actingAs($admin)
            ->get(route('admin.branches.index'))
            ->assertOk()
            ->assertSee('ویرایش', false)
            ->assertSee('حذف', false);
        $this->assertTrue($branch->is_default);
        $this->assertArrayNotHasKey('code', $branch->getAttributes());
        $this->assertArrayNotHasKey('gps_lat', $branch->getAttributes());

        $this->actingAs($admin)
            ->put(route('admin.branches.update', $branch), [
                'name' => 'South',
                'province' => 'Tehran',
                'city' => 'Tehran',
                'address' => 'Street 1',
                'phone' => '02100000000',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.branches.edit', $branch));

        $this->assertSame('South', $branch->fresh()->name);
        $this->assertFalse($branch->fresh()->is_default);

        $this->actingAs($admin)
            ->delete(route('admin.branches.destroy', $branch));

        $this->assertSoftDeleted($branch);
    }

    public function test_admin_can_create_a_technician_with_required_branch_and_skills(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $branch = Branch::factory()->create();
        $skill = Skill::factory()->create(['name' => 'Repair']);
        $this->actingAs($admin)
            ->post(route('admin.technicians.store'), [
                'first_name' => 'Ali',
                'last_name' => 'Rezaei',
                'mobile' => '09120001122',
                'branch_id' => $branch->id,
                'password' => 'secret',
                'password_confirmation' => 'secret',
                'skill_ids' => [$skill->id],
            ]);

        $technician = Technician::query()->where('name', 'Ali Rezaei')->first();

        $this->assertNotNull($technician);
        $this->assertSame($branch->id, $technician->branch_id);
        $this->assertNotNull($technician->user_id);
        $this->assertSame('09120001122', $technician->user?->username);
        $this->assertTrue($technician->user?->hasRole(RoleName::Technician));
        $this->assertEqualsCanonicalizing([$skill->id], $technician->skills()->pluck('skills.id')->all());
        $this->assertArrayNotHasKey('phone', $technician->getAttributes());
        $this->assertArrayNotHasKey('employee_code', $technician->getAttributes());

        $this->actingAs($admin)
            ->from(route('admin.technicians.create'))
            ->post(route('admin.technicians.store'), ['first_name' => 'No Branch'])
            ->assertRedirect(route('admin.technicians.create'))
            ->assertSessionHasErrors('branch_id');

        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'username' => '09120001122',
            'password' => 'secret',
        ])->assertRedirect(route('technician.dashboard'));
    }

    public function test_technician_mobile_must_start_with_09(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $branch = Branch::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.technicians.create'))
            ->post(route('admin.technicians.store'), [
                'first_name' => 'Ali',
                'last_name' => 'Rezaei',
                'mobile' => '08120001122',
                'branch_id' => $branch->id,
                'password' => 'secret',
                'password_confirmation' => 'secret',
            ])
            ->assertRedirect(route('admin.technicians.create'))
            ->assertSessionHasErrors('mobile');
    }

    public function test_admin_can_delete_a_technician_from_the_list(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $technician = Technician::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.technicians.destroy', $technician))
            ->assertRedirect(route('admin.technicians.index'));

        $this->assertSoftDeleted($technician);
    }

    public function test_operator_cannot_open_admin_technician_catalog(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('admin.skills.index'))
            ->assertForbidden();

        $this->actingAs($operator)
            ->get(route('admin.technicians.index'))
            ->assertForbidden();

        $this->actingAs($operator)
            ->get(route('admin.branches.index'))
            ->assertForbidden();
    }
}
