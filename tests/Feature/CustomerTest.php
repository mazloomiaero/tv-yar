<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionName;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_customer_search_shows_add_customer_button(): void
    {
        $this->actingAs($this->staffUser(RoleName::CompanyAdmin))
            ->get(route('admin.customers.search'))
            ->assertOk()
            ->assertSee('افزودن مشتری')
            ->assertSee(route('admin.customers.create'), false);
    }

    public function test_operator_can_search_customers_by_mobile(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        Customer::factory()->create([
            'customer_code' => 'C-001',
            'first_name' => 'Ada',
            'mobile' => '09120000001',
        ]);
        Customer::factory()->create([
            'customer_code' => 'C-003',
            'mobile' => '09129999999',
        ]);

        $this->actingAs($operator)
            ->get(route('operator.customers.search', ['mobile' => '09120000001']))
            ->assertOk()
            ->assertSee('C-001')
            ->assertSee('Ada')
            ->assertDontSee('C-003');
    }

    public function test_operator_can_search_customers_by_first_and_last_name(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        Customer::factory()->create([
            'customer_code' => 'C-010',
            'first_name' => 'Ali',
            'last_name' => 'Rezaei',
            'mobile' => '09120000010',
        ]);
        Customer::factory()->create([
            'customer_code' => 'C-011',
            'first_name' => 'Sara',
            'last_name' => 'Karimi',
            'mobile' => '09120000011',
        ]);

        $this->actingAs($operator)
            ->get(route('operator.customers.search', ['q' => 'Ali']))
            ->assertOk()
            ->assertSee('C-010')
            ->assertDontSee('C-011');

        $this->actingAs($operator)
            ->get(route('operator.customers.search', ['q' => 'Karimi']))
            ->assertOk()
            ->assertSee('C-011')
            ->assertDontSee('C-010');

        $this->actingAs($operator)
            ->get(route('operator.customers.search', ['q' => 'Ali Rezaei']))
            ->assertOk()
            ->assertSee('C-010')
            ->assertDontSee('C-011');
    }

    public function test_operator_search_with_no_match_offers_create(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('operator.customers.search', ['mobile' => '09128888888']))
            ->assertOk()
            ->assertSee('مشتری با این شماره یافت نشد.')
            ->assertSee('ایجاد مشتری با این شماره')
            ->assertSee('09128888888');
    }

    public function test_operator_can_create_a_customer(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $response = $this->actingAs($operator)
            ->post(route('operator.customers.store'), $this->customerPayload());

        $customer = Customer::query()->where('customer_code', 'CUS-100')->first();

        $this->assertNotNull($customer);
        $this->assertSame('09121112233', $customer->mobile);
        $this->assertNull($customer->user_id);

        $response->assertRedirect(route('operator.customers.edit', $customer));
    }

    public function test_operator_can_create_customer_portal_login(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->post(route('operator.customers.store'), $this->customerPayload([
                'portal_username' => 'cust1',
                'portal_password' => 'test',
                'portal_password_confirmation' => 'test',
            ]));

        $customer = Customer::query()->where('customer_code', 'CUS-100')->first();

        $this->assertNotNull($customer);
        $this->assertNotNull($customer->user_id);
        $this->assertSame('cust1', $customer->user?->username);
    }

    public function test_duplicate_mobile_is_rejected_on_create(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        Customer::factory()->create([
            'customer_code' => 'CUS-200',
            'mobile' => '09121112233',
        ]);

        $this->actingAs($operator)
            ->from(route('operator.customers.create'))
            ->post(route('operator.customers.store'), $this->customerPayload([
                'customer_code' => 'CUS-201',
            ]))
            ->assertRedirect(route('operator.customers.create'))
            ->assertSessionHasErrors('mobile');

        $this->assertSame(1, Customer::query()->where('mobile', '09121112233')->count());
    }

    public function test_invalid_mobile_format_is_rejected_on_create(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->from(route('operator.customers.create'))
            ->post(route('operator.customers.store'), $this->customerPayload([
                'mobile' => '12345678901',
            ]))
            ->assertRedirect(route('operator.customers.create'))
            ->assertSessionHasErrors('mobile');

        $this->assertNull(Customer::query()->where('customer_code', 'CUS-100')->first());
    }

    public function test_mobile_of_a_soft_deleted_customer_can_be_reused(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $deleted = Customer::factory()->create([
            'customer_code' => 'CUS-200',
            'mobile' => '09121112233',
        ]);
        $deleted->delete();

        $this->actingAs($operator)
            ->post(route('operator.customers.store'), $this->customerPayload([
                'customer_code' => 'CUS-201',
            ]))
            ->assertRedirect();

        $this->assertSame(1, Customer::query()->where('mobile', '09121112233')->count());
        $this->assertNotNull(Customer::query()->where('customer_code', 'CUS-201')->first());
    }

    public function test_duplicate_mobile_is_rejected_on_update(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        Customer::factory()->create([
            'customer_code' => 'CUS-400',
            'mobile' => '09124445566',
        ]);
        $customer = Customer::factory()->create([
            'customer_code' => 'CUS-401',
            'mobile' => '09120000002',
        ]);

        $this->actingAs($operator)
            ->from(route('operator.customers.edit', $customer))
            ->put(route('operator.customers.update', $customer), $this->customerPayload([
                'customer_code' => 'CUS-401',
                'mobile' => '09124445566',
            ]))
            ->assertRedirect(route('operator.customers.edit', $customer))
            ->assertSessionHasErrors('mobile');

        $customer->refresh();

        $this->assertSame('09120000002', $customer->mobile);
    }

    public function test_duplicate_customer_code_is_rejected(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        Customer::factory()->create(['customer_code' => 'CUS-100']);

        $this->actingAs($operator)
            ->from(route('operator.customers.create'))
            ->post(route('operator.customers.store'), $this->customerPayload())
            ->assertRedirect(route('operator.customers.create'))
            ->assertSessionHasErrors('customer_code');
    }

    public function test_create_validation_failure(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->from(route('operator.customers.create'))
            ->post(route('operator.customers.store'), [])
            ->assertRedirect(route('operator.customers.create'))
            ->assertSessionHasErrors(['customer_code', 'first_name', 'last_name', 'mobile', 'status']);
    }

    public function test_operator_input_cannot_set_user_id(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $linked = User::factory()->create();

        $this->actingAs($operator)
            ->post(route('operator.customers.store'), $this->customerPayload([
                'user_id' => $linked->id,
            ]));

        $customer = Customer::query()->where('customer_code', 'CUS-100')->first();

        $this->assertNotNull($customer);
        $this->assertNull($customer->user_id);
    }

    public function test_operator_can_update_a_customer(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $customer = Customer::factory()->create([
            'customer_code' => 'CUS-300',
            'first_name' => 'Old',
            'mobile' => '09120000002',
        ]);

        $this->actingAs($operator)
            ->put(route('operator.customers.update', $customer), $this->customerPayload([
                'customer_code' => 'CUS-300',
                'first_name' => 'New',
                'mobile' => '09120000002',
            ]))
            ->assertRedirect(route('operator.customers.edit', $customer));

        $customer->refresh();

        $this->assertSame('New', $customer->first_name);
        $this->assertSame('CUS-300', $customer->customer_code);
    }

    public function test_update_does_not_change_user_id(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $linked = User::factory()->create();
        $customer = Customer::factory()->create([
            'customer_code' => 'CUS-301',
            'user_id' => $linked->id,
        ]);

        $this->actingAs($operator)
            ->put(route('operator.customers.update', $customer), $this->customerPayload([
                'customer_code' => 'CUS-301',
                'user_id' => User::factory()->create()->id,
            ]));

        $customer->refresh();

        $this->assertSame($linked->id, $customer->user_id);
    }

    public function test_operator_without_customer_permissions_is_forbidden(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $role = Role::query()->where('name', RoleName::Operator->value)->firstOrFail();
        $role->permissions()->detach(
            Permission::query()->whereIn('name', [
                PermissionName::CustomersView->value,
                PermissionName::CustomersCreate->value,
                PermissionName::CustomersEdit->value,
                PermissionName::CustomersDelete->value,
            ])->pluck('id')
        );

        $this->actingAs($operator)
            ->get(route('operator.customers.search'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_customer_search(): void
    {
        $this->get(route('operator.customers.search'))
            ->assertRedirect(route('login'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function customerPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_code' => 'CUS-100',
            'first_name' => 'Ali',
            'last_name' => 'Rezaei',
            'mobile' => '09121112233',
            'mobile_secondary' => '',
            'email' => '',
            'national_id' => null,
            'notes' => '',
            'status' => 'active',
        ], $overrides);
    }
}
