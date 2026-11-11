<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\PermissionName;
use App\Support\RoleName;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_six_roles_and_sample_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        foreach (RoleName::values() as $name) {
            $this->assertDatabaseHas('roles', ['name' => $name]);
        }

        foreach (PermissionName::values() as $name) {
            $this->assertDatabaseHas('permissions', ['name' => $name]);
        }

        $this->assertSame(6, DB::table('roles')->count());
        $this->assertSame(count(PermissionName::values()), DB::table('permissions')->count());
        $this->assertSame(
            count(PermissionName::values()) + 14 + 3 + 4 + 5,
            DB::table('role_permissions')->count()
        );

        $operatorId = DB::table('roles')->where('name', RoleName::Operator->value)->value('id');
        $this->assertSame(14, DB::table('role_permissions')->where('role_id', $operatorId)->count());
        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $operatorId,
            'permission_id' => DB::table('permissions')->where('name', PermissionName::FeedbackView->value)->value('id'),
        ]);
        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $operatorId,
            'permission_id' => DB::table('permissions')->where('name', PermissionName::RequestsAssign->value)->value('id'),
        ]);

        $technicalId = DB::table('roles')->where('name', RoleName::TechnicalSupervisor->value)->value('id');
        $this->assertSame(3, DB::table('role_permissions')->where('role_id', $technicalId)->count());
        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $technicalId,
            'permission_id' => DB::table('permissions')->where('name', PermissionName::RequestsAssign->value)->value('id'),
        ]);
        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $technicalId,
            'permission_id' => DB::table('permissions')->where('name', PermissionName::RequestsChangeStatus->value)->value('id'),
        ]);

        $technicianId = DB::table('roles')->where('name', RoleName::Technician->value)->value('id');
        $this->assertSame(4, DB::table('role_permissions')->where('role_id', $technicianId)->count());
        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $technicianId,
            'permission_id' => DB::table('permissions')->where('name', PermissionName::PaymentsCreate->value)->value('id'),
        ]);

        $accountantId = DB::table('roles')->where('name', RoleName::Accountant->value)->value('id');
        $this->assertSame(5, DB::table('role_permissions')->where('role_id', $accountantId)->count());
        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $accountantId,
            'permission_id' => DB::table('permissions')->where('name', PermissionName::PaymentsVerify->value)->value('id'),
        ]);
    }
}
