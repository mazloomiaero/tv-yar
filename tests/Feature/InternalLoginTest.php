<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Support\PermissionName;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_staff_can_log_in_with_username_and_password_and_reach_their_panel(): void
    {
        $this->staffUser(RoleName::Operator, [
            'username' => 'ops1',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'username' => 'ops1',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('operator.dashboard'));
        $this->assertAuthenticatedAs(User::query()->where('username', 'ops1')->first(), 'web');
        $this->get(route('operator.dashboard'))->assertOk();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->staffUser(RoleName::Operator, [
            'username' => 'ops1',
            'password' => 'password',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'username' => 'ops1',
                'password' => 'wrong',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest('web');
    }

    public function test_user_without_a_staff_role_cannot_log_in(): void
    {
        User::factory()->create([
            'username' => 'norole',
            'password' => 'password',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'username' => 'norole',
                'password' => 'password',
            ])
            ->assertSessionHasErrors('username');

        $this->assertGuest('web');
    }

    public function test_staff_can_log_out(): void
    {
        $user = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest('web');
    }

    public function test_internal_login_is_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), [
                'username' => 'unknown',
                'password' => 'password',
            ])->assertSessionHasErrors('username');
        }

        $this->post(route('login.store'), [
            'username' => 'unknown',
            'password' => 'password',
        ])->assertStatus(429);
    }

    public function test_company_admin_has_all_sample_permissions_and_operator_has_customer_permissions(): void
    {
        $admin = $this->staffUser(RoleName::CompanyAdmin);
        $operator = $this->staffUser(RoleName::Operator);

        $this->assertTrue($admin->can(PermissionName::CustomersView->value));
        $this->assertTrue($operator->can(PermissionName::CustomersView->value));
        $this->assertTrue($operator->can(PermissionName::RequestsView->value));
        $this->assertFalse($operator->can(PermissionName::PaymentsVerify->value));
    }
}
