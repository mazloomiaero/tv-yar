<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_open_and_update_profile_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'portaluser',
            'password' => 'oldpass',
        ]);
        $customer = Customer::factory()->create([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'mobile' => '09120000001',
            'customer_code' => 'CUS-KEEP',
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.profile.edit'))
            ->assertOk()
            ->assertSee('پروفایل')
            ->assertSee('CUS-KEEP');

        $this->actingAs($customer, 'customer')
            ->from(route('customer.profile.edit'))
            ->put(route('customer.profile.update'), [
                'first_name' => 'Grace',
                'last_name' => 'Hopper',
                'mobile' => '09121112233',
                'mobile_secondary' => '09123334455',
                'email' => 'grace@example.com',
                'national_id' => '0012345678',
                'portal_username' => 'portaluser',
                'portal_password' => 'newpass',
                'portal_password_confirmation' => 'newpass',
                'customer_code' => 'HACKED',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('customer.profile.edit'));

        $customer->refresh();

        $this->assertSame('Grace', $customer->first_name);
        $this->assertSame('Hopper', $customer->last_name);
        $this->assertSame('09121112233', $customer->mobile);
        $this->assertSame('CUS-KEEP', $customer->customer_code);
        $this->assertSame('active', $customer->status);
        $this->assertTrue(Hash::check('newpass', (string) $customer->user?->password));
    }

    public function test_profile_validation_failure(): void
    {
        $user = User::factory()->create(['username' => 'portaluser']);
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->actingAs($customer, 'customer')
            ->from(route('customer.profile.edit'))
            ->put(route('customer.profile.update'), [])
            ->assertRedirect(route('customer.profile.edit'))
            ->assertSessionHasErrors(['first_name', 'last_name', 'mobile']);
    }

    public function test_guest_is_redirected_from_profile(): void
    {
        $this->get(route('customer.profile.edit'))
            ->assertRedirect(route('customer.login'));
    }
}
