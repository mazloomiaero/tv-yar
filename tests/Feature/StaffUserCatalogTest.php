<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffUserCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_can_create_an_operator_staff_user(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'first_name' => 'Sara',
                'last_name' => 'Ahmadi',
                'mobile' => '09121234567',
                'username' => 'sara',
                'email' => 'sara@example.com',
                'password' => 'secret',
                'password_confirmation' => 'secret',
                'role' => RoleName::Operator->value,
            ]);

        $staff = User::query()->where('username', 'sara')->first();

        $this->assertNotNull($staff);
        $this->assertSame('Sara', $staff->first_name);
        $this->assertSame('Ahmadi', $staff->last_name);
        $this->assertSame('09121234567', $staff->mobile);
        $this->assertTrue($staff->hasRole(RoleName::Operator));

        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'username' => 'sara',
            'password' => 'secret',
        ])->assertRedirect(route('operator.dashboard'));
    }

    public function test_operator_cannot_open_staff_catalog(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('admin.staff.index'))
            ->assertForbidden();
    }

    public function test_admin_can_open_operator_request_list(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->get(route('operator.service-requests.index'))
            ->assertRedirect(route('admin.service-requests.index'));

        $this->actingAs($admin)
            ->get(route('admin.service-requests.index'))
            ->assertOk();
    }

    public function test_staff_mobile_must_be_eleven_digits_starting_with_09(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->from(route('admin.staff.create'))
            ->post(route('admin.staff.store'), [
                'first_name' => 'Sara',
                'last_name' => 'Ahmadi',
                'mobile' => '9121234567',
                'username' => 'sara2',
                'password' => 'secret',
                'password_confirmation' => 'secret',
                'role' => RoleName::Operator->value,
            ])
            ->assertRedirect(route('admin.staff.create'))
            ->assertSessionHasErrors('mobile');
    }

    public function test_admin_can_delete_staff_from_the_list(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($admin)
            ->delete(route('admin.staff.destroy', $operator))
            ->assertRedirect(route('admin.staff.index'));

        $this->assertSoftDeleted($operator);
    }
}
