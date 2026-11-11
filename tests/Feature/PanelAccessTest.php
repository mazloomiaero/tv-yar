<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_operator_cannot_open_the_admin_panel(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_open_the_operator_panel(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->get(route('operator.dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.customers.search'))
            ->assertOk();
    }

    public function test_admin_cannot_open_the_technician_panel(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->get(route('technician.dashboard'))
            ->assertForbidden();
    }

    public function test_staff_user_cannot_open_the_customer_panel(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->get(route('customer.dashboard'))
            ->assertForbidden();
    }

    public function test_customer_cannot_open_staff_panels(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer, 'customer')
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_matching_roles_can_open_their_panels(): void
    {
        $cases = [
            [RoleName::CompanyAdmin, 'admin.dashboard'],
            [RoleName::Operator, 'operator.dashboard'],
            [RoleName::TechnicalSupervisor, 'technical.dashboard'],
            [RoleName::Accountant, 'accountant.dashboard'],
            [RoleName::Technician, 'technician.dashboard'],
        ];

        foreach ($cases as [$role, $route]) {
            $user = $this->staffUser($role);

            $this->actingAs($user)
                ->get(route($route))
                ->assertOk();
        }
    }
}
