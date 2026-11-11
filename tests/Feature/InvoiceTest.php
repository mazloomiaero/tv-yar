<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Invoice\CreateInvoice;
use App\Events\InvoiceCreated;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\Invoice;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Support\InitialServiceStatus;
use App\Support\InvoiceStatus;
use App\Support\PermissionName;
use App\Support\RoleName;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_accountant_creates_invoice_from_current_report_parts_with_snapshots(): void
    {
        Event::fake([InvoiceCreated::class]);

        $accountant = $this->staffUser(RoleName::Accountant);
        $context = $this->invoiceContext([
            'first_name' => 'Sara',
            'last_name' => 'Karimi',
            'mobile' => '09120000001',
        ]);

        $response = $this->actingAs($accountant)
            ->post(route('accountant.service-requests.invoices.store', $context['request']), [
                'discount' => '10.00',
                'tax' => '5.00',
                'total' => '1.00',
            ]);

        $invoice = Invoice::query()->first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('accountant.invoices.show', $invoice));

        $this->assertSame('INV-1405-000001', $invoice->invoice_number);
        $this->assertSame($context['request']->id, $invoice->service_request_id);
        $this->assertSame($context['customer']->id, $invoice->customer_id);
        $this->assertSame('Sara Karimi', $invoice->customer_name);
        $this->assertSame('09120000001', $invoice->customer_mobile);
        $this->assertSame('', $invoice->customer_address);
        $this->assertSame('31.50', (string) $invoice->subtotal);
        $this->assertSame('10.00', (string) $invoice->discount);
        $this->assertSame('5.00', (string) $invoice->tax);
        $this->assertSame('26.50', (string) $invoice->total);
        $this->assertSame(InvoiceStatus::PendingPayment->value, $invoice->status);
        $this->assertNotNull($invoice->issued_at);
        $this->assertNull($invoice->paid_at);
        $this->assertSame(2, $invoice->items()->count());

        $catalogLine = $invoice->items()->where('title', 'Compressor')->first();
        $this->assertNotNull($catalogLine);
        $this->assertSame('2.500', (string) $catalogLine->quantity);
        $this->assertSame('10.00', (string) $catalogLine->unit_price);
        $this->assertSame('25.00', (string) $catalogLine->total);
        $this->assertSame('', $catalogLine->description);

        $freeLine = $invoice->items()->where('title', 'Free gasket')->first();
        $this->assertNotNull($freeLine);
        $this->assertSame('6.50', (string) $freeLine->total);

        $this->assertSame(0, DB::table('payments')->count());
        $this->assertSame(0, DB::table('payment_transactions')->count());
        $this->assertFalse(Schema::hasColumn('service_report_parts', 'is_confirmed'));

        Event::assertDispatched(
            InvoiceCreated::class,
            fn (InvoiceCreated $event): bool => $event->invoiceId === $invoice->id,
        );

        $this->actingAs($accountant)
            ->get(route('accountant.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('INV-1405-000001')
            ->assertSee('Sara Karimi')
            ->assertSee('Compressor')
            ->assertSee('Free gasket');
    }

    public function test_creating_an_invoice_twice_for_the_same_request_is_rejected(): void
    {
        Event::fake([InvoiceCreated::class]);

        $context = $this->invoiceContext();
        $createInvoice = app(CreateInvoice::class);
        $attributes = [
            'discount' => '0',
            'tax' => '0',
        ];
        $year = (int) config('document.year');

        $invoice = $createInvoice->execute($context['request'], $attributes, $year);

        try {
            $createInvoice->execute($context['request'], $attributes, $year);
            $this->fail('A second invoice for the same service request must be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString($invoice->invoice_number, $exception->getMessage());
        }

        $this->assertSame(1, Invoice::query()->count());
        $this->assertSame(1, Invoice::query()->where('service_request_id', $context['request']->id)->count());
        $this->assertSame(2, $invoice->items()->count());
        $this->assertSame($invoice->id, Invoice::query()->sole()->id);

        $indexes = collect(Schema::getIndexes('invoices'));
        $this->assertTrue(
            $indexes->contains(
                fn (array $index): bool => in_array('service_request_id', $index['columns'], true) && $index['unique'],
            ),
        );

        Event::assertDispatchedTimes(InvoiceCreated::class, 1);
    }

    public function test_invoice_customer_snapshot_does_not_change_when_live_customer_is_updated(): void
    {
        $accountant = $this->staffUser(RoleName::Accountant);
        $context = $this->invoiceContext([
            'first_name' => 'Sara',
            'last_name' => 'Karimi',
            'mobile' => '09120000001',
        ]);

        $this->actingAs($accountant)
            ->post(route('accountant.service-requests.invoices.store', $context['request']), [
                'discount' => '0',
                'tax' => '0',
            ]);

        $invoice = Invoice::query()->first();
        $this->assertNotNull($invoice);

        $context['customer']->update([
            'first_name' => 'Changed',
            'last_name' => 'Name',
            'mobile' => '09129999999',
        ]);

        $this->assertSame('Sara Karimi', $invoice->fresh()->customer_name);
        $this->assertSame('09120000001', $invoice->fresh()->customer_mobile);
    }

    public function test_invoice_is_rejected_when_the_request_has_no_report_parts(): void
    {
        $accountant = $this->staffUser(RoleName::Accountant);
        $context = $this->invoiceContext(withParts: false);

        $this->actingAs($accountant)
            ->from(route('accountant.service-requests.invoices.create', $context['request']))
            ->post(route('accountant.service-requests.invoices.store', $context['request']), [
                'discount' => '0',
                'tax' => '0',
            ])
            ->assertRedirect(route('accountant.service-requests.invoices.create', $context['request']))
            ->assertSessionHasErrors('discount');

        $this->assertSame(0, Invoice::query()->count());
    }

    public function test_discount_greater_than_subtotal_is_rejected(): void
    {
        $accountant = $this->staffUser(RoleName::Accountant);
        $context = $this->invoiceContext();

        $this->actingAs($accountant)
            ->from(route('accountant.service-requests.invoices.create', $context['request']))
            ->post(route('accountant.service-requests.invoices.store', $context['request']), [
                'discount' => '999.00',
                'tax' => '0',
            ])
            ->assertRedirect(route('accountant.service-requests.invoices.create', $context['request']))
            ->assertSessionHasErrors('discount');

        $this->assertSame(0, Invoice::query()->count());
    }

    public function test_operator_cannot_create_an_invoice_in_the_accountant_panel(): void
    {
        $operator = $this->staffUser(RoleName::Operator);
        $context = $this->invoiceContext();

        $this->actingAs($operator)
            ->post(route('accountant.service-requests.invoices.store', $context['request']), [
                'discount' => '0',
                'tax' => '0',
            ])
            ->assertForbidden();
    }

    public function test_accountant_without_create_permission_is_forbidden(): void
    {
        $accountant = $this->staffUser(RoleName::Accountant);
        $role = Role::query()->where('name', RoleName::Accountant->value)->firstOrFail();
        $role->permissions()->detach(
            Permission::query()->where('name', PermissionName::InvoicesCreate->value)->pluck('id')
        );
        $context = $this->invoiceContext();

        $this->actingAs($accountant)
            ->post(route('accountant.service-requests.invoices.store', $context['request']), [
                'discount' => '0',
                'tax' => '0',
            ])
            ->assertForbidden();
    }

    public function test_customer_can_view_own_invoice_but_cannot_create(): void
    {
        $accountant = $this->staffUser(RoleName::Accountant);
        $context = $this->invoiceContext();

        $this->actingAs($accountant)
            ->post(route('accountant.service-requests.invoices.store', $context['request']), [
                'discount' => '0',
                'tax' => '0',
            ]);

        $invoice = Invoice::query()->first();
        $this->assertNotNull($invoice);

        Auth::guard('web')->logout();

        $this->actingAs($context['customer'], 'customer')
            ->get(route('customer.invoices.show', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number)
            ->assertSee('Compressor');

        $this->actingAs($context['customer'], 'customer')
            ->post(route('accountant.service-requests.invoices.store', $context['request']), [
                'discount' => '0',
                'tax' => '0',
            ])
            ->assertForbidden();
    }

    public function test_customer_cannot_view_another_customers_invoice(): void
    {
        $accountant = $this->staffUser(RoleName::Accountant);
        $context = $this->invoiceContext();

        $this->actingAs($accountant)
            ->post(route('accountant.service-requests.invoices.store', $context['request']), [
                'discount' => '0',
                'tax' => '0',
            ]);

        $invoice = Invoice::query()->first();
        $this->assertNotNull($invoice);

        Auth::guard('web')->logout();

        $other = Customer::factory()->create();

        $this->actingAs($other, 'customer')
            ->get(route('customer.invoices.show', $invoice))
            ->assertNotFound();
    }

    public function test_invoices_are_not_soft_deleted_entities(): void
    {
        $invoice = Invoice::factory()->create();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
        $this->assertNotContains(
            SoftDeletes::class,
            class_uses_recursive(Invoice::class),
        );
    }

    /**
     * @param  array{first_name?: string, last_name?: string, mobile?: string}  $customerAttributes
     * @return array{request: ServiceRequest, customer: Customer}
     */
    private function invoiceContext(array $customerAttributes = [], bool $withParts = true): array
    {
        $deviceType = DeviceType::factory()->create(['name' => 'TV']);
        $customer = Customer::factory()->create($customerAttributes);
        $device = Device::factory()->for($customer)->create([
            'device_type_id' => $deviceType->id,
        ]);
        $status = ServiceStatus::factory()->create(['name' => InitialServiceStatus::NAME]);
        $serviceType = ServiceType::factory()->create(['name' => 'Repair']);
        $serviceType->deviceTypes()->sync([$deviceType->id]);
        $serviceType->statuses()->sync([$status->id]);
        $address = CustomerAddress::factory()->for($customer)->create();
        $request = ServiceRequest::factory()->create([
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'service_type_id' => $serviceType->id,
            'address_id' => $address->id,
            'status_id' => $status->id,
        ]);

        if ($withParts) {
            $report = ServiceReport::factory()->create([
                'service_request_id' => $request->id,
            ]);
            $report->parts()->create([
                'part_id' => null,
                'name' => 'Compressor',
                'quantity' => '2.5',
                'unit_price' => '10.00',
                'total_price' => '1.00',
            ]);
            $report->parts()->create([
                'part_id' => null,
                'name' => 'Free gasket',
                'quantity' => '1',
                'unit_price' => '6.50',
                'total_price' => '999.00',
            ]);
        }

        return [
            'request' => $request,
            'customer' => $customer,
        ];
    }
}
