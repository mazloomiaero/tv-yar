<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPasswordLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_log_in_with_username_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'test',
            'password' => 'test',
        ]);
        $customer = Customer::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->post(route('customer.login.store'), [
            'username' => 'test',
            'password' => 'test',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticatedAs($customer, 'customer');
        $this->get(route('customer.dashboard'))->assertOk();
    }

    public function test_invalid_customer_password_is_rejected(): void
    {
        $user = User::factory()->create([
            'username' => 'test',
            'password' => 'test',
        ]);
        Customer::factory()->create(['user_id' => $user->id]);

        $this->from(route('customer.login'))
            ->post(route('customer.login.store'), [
                'username' => 'test',
                'password' => 'wrong',
            ])
            ->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest('customer');
    }

    public function test_staff_username_cannot_open_customer_panel(): void
    {
        $this->seedRbac();

        $this->staffUser(RoleName::Operator, [
            'username' => 'operator1',
            'password' => 'secret',
        ]);

        $this->from(route('customer.login'))
            ->post(route('customer.login.store'), [
                'username' => 'operator1',
                'password' => 'secret',
            ])
            ->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors('username');
    }
}
