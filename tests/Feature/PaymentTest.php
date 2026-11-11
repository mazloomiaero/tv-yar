<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\PaymentConfirmed;
use App\Events\PaymentInitiated;
use App\Jobs\VerifyPaymentJob;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ServiceAssignment;
use App\Models\ServiceRequest;
use App\Models\Technician;
use App\Models\User;
use App\Services\Payment\FakeGateway;
use App\Support\InvoiceStatus;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use App\Support\PaymentTransactionStatus;
use App\Support\PermissionName;
use App\Support\RoleName;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
        FakeGateway::$verifySucceeds = true;
        Http::preventStrayRequests();
    }

    public function test_customer_gateway_payment_succeeds_only_after_verify(): void
    {
        Event::fake([PaymentInitiated::class, PaymentConfirmed::class]);

        $customer = Customer::factory()->create();
        $invoice = $this->invoiceForCustomer($customer, '100.00');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.invoices.payments.gateway', $invoice))
            ->assertRedirect();

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);
        $this->assertSame(PaymentMethod::Gateway->value, $payment->method);
        $this->assertSame(PaymentStatus::Initiated->value, $payment->status);
        $this->assertNotSame('', $payment->reference_number);
        $this->assertNull($payment->paid_at);
        $this->assertSame(InvoiceStatus::PendingPayment->value, $invoice->fresh()->status);

        $transaction = $payment->transaction;
        $this->assertNotNull($transaction);
        $this->assertSame(PaymentTransactionStatus::Initiated->value, $transaction->status);
        $this->assertArrayNotHasKey('merchant_id', $transaction->request_data ?? []);
        $this->assertArrayNotHasKey('card_number', $transaction->request_data ?? []);

        Event::assertDispatched(
            PaymentInitiated::class,
            fn (PaymentInitiated $event): bool => $event->paymentId === $payment->id,
        );
        Event::assertNotDispatched(PaymentConfirmed::class);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.payments.callback', [
                'Authority' => $transaction->authority,
                'Status' => 'OK',
            ]))
            ->assertRedirect(route('customer.invoices.show', $invoice));

        $payment = $payment->fresh();
        $this->assertSame(PaymentStatus::Completed->value, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame(PaymentTransactionStatus::Verified->value, $payment->transaction?->status);
        $this->assertSame(InvoiceStatus::Paid->value, $invoice->fresh()->status);
        $this->assertNotNull($invoice->fresh()->paid_at);

        Event::assertDispatched(
            PaymentConfirmed::class,
            fn (PaymentConfirmed $event): bool => $event->paymentId === $payment->id,
        );
    }

    public function test_gateway_redirect_without_ok_status_is_not_success(): void
    {
        $customer = Customer::factory()->create();
        $invoice = $this->invoiceForCustomer($customer, '50.00');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.invoices.payments.gateway', $invoice));

        $transaction = PaymentTransaction::query()->first();
        $this->assertNotNull($transaction);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.payments.callback', [
                'Authority' => $transaction->authority,
                'Status' => 'NOK',
            ]))
            ->assertRedirect(route('customer.invoices.show', $invoice));

        $this->assertSame(PaymentStatus::Initiated->value, Payment::query()->first()?->status);
        $this->assertSame(InvoiceStatus::PendingPayment->value, $invoice->fresh()->status);
        $this->assertSame(0, Payment::query()->where('status', PaymentStatus::Completed->value)->count());
    }

    public function test_failed_verify_does_not_complete_payment_or_dispatch_payment_failed(): void
    {
        Event::fake();
        FakeGateway::$verifySucceeds = false;

        $customer = Customer::factory()->create();
        $invoice = $this->invoiceForCustomer($customer, '50.00');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.invoices.payments.gateway', $invoice));

        $transaction = PaymentTransaction::query()->first();
        $this->assertNotNull($transaction);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.payments.callback', [
                'Authority' => $transaction->authority,
                'Status' => 'OK',
            ]))
            ->assertRedirect(route('customer.invoices.show', $invoice));

        $this->assertSame(PaymentStatus::Initiated->value, Payment::query()->first()?->status);
        $this->assertSame(PaymentTransactionStatus::Failed->value, $transaction->fresh()->status);
        $this->assertSame(InvoiceStatus::PendingPayment->value, $invoice->fresh()->status);
        Event::assertNotDispatched(PaymentConfirmed::class);
        $this->assertFalse(class_exists('App\\Events\\PaymentFailed'));
    }

    public function test_guest_gateway_callback_still_verifies_without_customer_session(): void
    {
        $customer = Customer::factory()->create();
        $invoice = $this->invoiceForCustomer($customer, '45.00');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.invoices.payments.gateway', $invoice));

        $transaction = PaymentTransaction::query()->first();
        $this->assertNotNull($transaction);

        $this->app['auth']->guard('customer')->logout();
        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->assertGuest('customer');

        $this->get(route('customer.payments.callback', [
            'Authority' => $transaction->authority,
            'Status' => 'OK',
        ]))->assertRedirect(route('customer.login'));

        $this->assertSame(PaymentStatus::Completed->value, Payment::query()->first()?->status);
        $this->assertSame(InvoiceStatus::Paid->value, $invoice->fresh()->status);
    }

    public function test_other_customer_on_callback_does_not_see_the_invoice_but_verify_still_runs(): void
    {
        $owner = Customer::factory()->create();
        $invoice = $this->invoiceForCustomer($owner, '15.00');
        $other = Customer::factory()->create();

        $this->actingAs($owner, 'customer')
            ->post(route('customer.invoices.payments.gateway', $invoice));

        $transaction = PaymentTransaction::query()->first();
        $this->assertNotNull($transaction);

        $this->actingAs($other, 'customer')
            ->get(route('customer.payments.callback', [
                'Authority' => $transaction->authority,
                'Status' => 'OK',
            ]))
            ->assertRedirect(route('customer.invoices.index'))
            ->assertSessionMissing('errors');

        $this->assertSame(PaymentStatus::Completed->value, Payment::query()->first()?->status);
    }

    public function test_duplicate_gateway_callback_does_not_double_complete(): void
    {
        Event::fake([PaymentConfirmed::class]);

        $customer = Customer::factory()->create();
        $invoice = $this->invoiceForCustomer($customer, '80.00');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.invoices.payments.gateway', $invoice));

        $transaction = PaymentTransaction::query()->first();
        $this->assertNotNull($transaction);

        $callback = route('customer.payments.callback', [
            'Authority' => $transaction->authority,
            'Status' => 'OK',
        ]);

        $this->actingAs($customer, 'customer')->get($callback)->assertRedirect();
        $this->actingAs($customer, 'customer')->get($callback)->assertRedirect();

        $this->assertSame(1, Payment::query()->count());
        $this->assertSame(1, Payment::query()->where('status', PaymentStatus::Completed->value)->count());
        $this->assertSame(
            '80.00',
            number_format(
                (float) $invoice->fresh()->payments()->where('status', PaymentStatus::Completed->value)->sum('amount'),
                2,
                '.',
                '',
            ),
        );
        Event::assertDispatchedTimes(PaymentConfirmed::class, 1);
    }

    public function test_verify_payment_job_confirms_gateway_payment(): void
    {
        $customer = Customer::factory()->create();
        $invoice = $this->invoiceForCustomer($customer, '20.00');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.invoices.payments.gateway', $invoice));

        $transaction = PaymentTransaction::query()->first();
        $this->assertNotNull($transaction);

        VerifyPaymentJob::dispatchSync($transaction->authority);

        $this->assertSame(PaymentStatus::Completed->value, Payment::query()->first()?->status);
    }

    public function test_customer_cannot_start_gateway_payment_for_another_customers_invoice(): void
    {
        $owner = Customer::factory()->create();
        $invoice = $this->invoiceForCustomer($owner, '10.00');
        $other = Customer::factory()->create();

        $this->actingAs($other, 'customer')
            ->post(route('customer.invoices.payments.gateway', $invoice))
            ->assertNotFound();
    }

    public function test_technician_records_card_transfer_pending_until_accountant_confirms(): void
    {
        Event::fake([PaymentInitiated::class, PaymentConfirmed::class]);
        Storage::fake('public');

        $technicianUser = $this->staffUser(RoleName::Technician);
        $context = $this->assignedInvoice($technicianUser, '100.00');
        $receipt = $this->pngUpload('receipt.png');

        $this->actingAs($technicianUser)
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::CardTransfer->value,
                'amount' => '100.00',
                'reference_number' => 'TRX-99',
                'receipt_image' => $receipt,
            ])
            ->assertRedirect(route('technician.payments.create', $context['invoice']));

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);
        $this->assertSame(PaymentMethod::CardTransfer->value, $payment->method);
        $this->assertSame(PaymentStatus::PendingConfirmation->value, $payment->status);
        $this->assertSame('TRX-99', $payment->reference_number);
        $this->assertSame($technicianUser->id, $payment->recorded_by);
        $this->assertNull($payment->paid_at);
        $this->assertNotNull($payment->receipt_image);
        $this->assertStringStartsWith('payment-receipts/', $payment->receipt_image);
        Storage::disk('public')->assertExists($payment->receipt_image);
        $this->assertSame(0, PaymentTransaction::query()->count());
        $this->assertSame(InvoiceStatus::PendingPayment->value, $context['invoice']->fresh()->status);
        $this->assertNull($context['invoice']->fresh()->paid_at);

        Event::assertDispatched(PaymentInitiated::class);
        Event::assertNotDispatched(PaymentConfirmed::class);

        $accountant = $this->staffUser(RoleName::Accountant);

        $this->actingAs($accountant)
            ->post(route('accountant.payments.confirm', $payment))
            ->assertRedirect(route('accountant.payments.show', $payment));

        $this->assertSame(PaymentStatus::Completed->value, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertSame(InvoiceStatus::Paid->value, $context['invoice']->fresh()->status);
        $this->assertNotNull($context['invoice']->fresh()->paid_at);
        Event::assertDispatched(PaymentConfirmed::class);
    }

    public function test_cash_receipt_is_optional_and_reference_number_is_always_persisted(): void
    {
        Storage::fake('public');

        $technicianUser = $this->staffUser(RoleName::Technician);
        $context = $this->assignedInvoice($technicianUser, '40.00');

        $this->actingAs($technicianUser)
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::Cash->value,
                'amount' => '40.00',
            ])
            ->assertRedirect(route('technician.payments.create', $context['invoice']));

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);
        $this->assertSame(PaymentMethod::Cash->value, $payment->method);
        $this->assertSame(PaymentStatus::PendingConfirmation->value, $payment->status);
        $this->assertNotSame('', $payment->reference_number);
        $this->assertNull($payment->receipt_image);
        $this->assertSame($technicianUser->id, $payment->recorded_by);
    }

    public function test_card_transfer_without_receipt_is_rejected(): void
    {
        $technicianUser = $this->staffUser(RoleName::Technician);
        $context = $this->assignedInvoice($technicianUser, '40.00');

        $this->actingAs($technicianUser)
            ->from(route('technician.payments.create', $context['invoice']))
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::CardTransfer->value,
                'amount' => '40.00',
                'reference_number' => 'TRX-1',
            ])
            ->assertRedirect(route('technician.payments.create', $context['invoice']))
            ->assertSessionHasErrors('receipt_image');

        $this->assertSame(0, Payment::query()->count());
    }

    public function test_partial_manual_confirmation_sets_partially_paid_then_paid(): void
    {
        Storage::fake('public');

        $technicianUser = $this->staffUser(RoleName::Technician);
        $context = $this->assignedInvoice($technicianUser, '100.00');
        $accountant = $this->staffUser(RoleName::Accountant);

        $this->actingAs($technicianUser)
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::Cash->value,
                'amount' => '40.00',
                'reference_number' => 'CASH-1',
            ]);
        $this->actingAs($technicianUser)
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::Cash->value,
                'amount' => '60.00',
                'reference_number' => 'CASH-2',
            ]);

        $first = Payment::query()->where('reference_number', 'CASH-1')->first();
        $second = Payment::query()->where('reference_number', 'CASH-2')->first();
        $this->assertNotNull($first);
        $this->assertNotNull($second);

        $this->actingAs($accountant)->post(route('accountant.payments.confirm', $first));

        $invoice = $context['invoice']->fresh();
        $this->assertSame(InvoiceStatus::PartiallyPaid->value, $invoice->status);
        $this->assertNull($invoice->paid_at);

        $this->actingAs($accountant)->post(route('accountant.payments.confirm', $second));

        $invoice = $context['invoice']->fresh();
        $this->assertSame(InvoiceStatus::Paid->value, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_duplicate_accountant_confirm_does_not_double_apply(): void
    {
        Event::fake([PaymentConfirmed::class]);

        $technicianUser = $this->staffUser(RoleName::Technician);
        $context = $this->assignedInvoice($technicianUser, '30.00');
        $accountant = $this->staffUser(RoleName::Accountant);

        $this->actingAs($technicianUser)
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::Cash->value,
                'amount' => '30.00',
                'reference_number' => 'CASH-DUP',
            ]);

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);

        $this->actingAs($accountant)->post(route('accountant.payments.confirm', $payment));
        $this->actingAs($accountant)->post(route('accountant.payments.confirm', $payment));

        Event::assertDispatchedTimes(PaymentConfirmed::class, 1);
        $this->assertSame(1, Payment::query()->where('status', PaymentStatus::Completed->value)->count());
    }

    public function test_unassigned_technician_cannot_record_manual_payment(): void
    {
        $assigned = $this->staffUser(RoleName::Technician);
        $other = $this->staffUser(RoleName::Technician);
        $context = $this->assignedInvoice($assigned, '10.00');

        $this->actingAs($other)
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::Cash->value,
                'amount' => '10.00',
            ])
            ->assertForbidden();
    }

    public function test_operator_cannot_record_or_confirm_payments(): void
    {
        $technicianUser = $this->staffUser(RoleName::Technician);
        $context = $this->assignedInvoice($technicianUser, '10.00');
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::Cash->value,
                'amount' => '10.00',
            ])
            ->assertForbidden();

        $this->actingAs($technicianUser)
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::Cash->value,
                'amount' => '10.00',
                'reference_number' => 'CASH-OP',
            ]);

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);

        $this->actingAs($operator)
            ->post(route('accountant.payments.confirm', $payment))
            ->assertForbidden();
    }

    public function test_accountant_without_verify_permission_is_forbidden(): void
    {
        $technicianUser = $this->staffUser(RoleName::Technician);
        $context = $this->assignedInvoice($technicianUser, '10.00');
        $accountant = $this->staffUser(RoleName::Accountant);
        $role = Role::query()->where('name', RoleName::Accountant->value)->firstOrFail();
        $role->permissions()->detach(
            Permission::query()->where('name', PermissionName::PaymentsVerify->value)->pluck('id')
        );

        $this->actingAs($technicianUser)
            ->post(route('technician.payments.store', $context['invoice']), [
                'method' => PaymentMethod::Cash->value,
                'amount' => '10.00',
                'reference_number' => 'CASH-PERM',
            ]);

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);

        $this->actingAs($accountant)
            ->post(route('accountant.payments.confirm', $payment))
            ->assertForbidden();
    }

    public function test_payments_are_not_soft_deleted_entities(): void
    {
        $payment = Payment::factory()->create();

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
        $this->assertNotContains(
            SoftDeletes::class,
            class_uses_recursive(Payment::class),
        );
        $this->assertNotContains(
            SoftDeletes::class,
            class_uses_recursive(PaymentTransaction::class),
        );
    }

    private function invoiceForCustomer(Customer $customer, string $total): Invoice
    {
        $request = ServiceRequest::factory()->create([
            'customer_id' => $customer->id,
        ]);

        return Invoice::factory()->create([
            'service_request_id' => $request->id,
            'customer_id' => $customer->id,
            'total' => $total,
            'status' => InvoiceStatus::PendingPayment->value,
            'paid_at' => null,
        ]);
    }

    /**
     * @return array{invoice: Invoice, request: ServiceRequest}
     */
    private function assignedInvoice(User $user, string $total): array
    {
        $request = ServiceRequest::factory()->create();
        $technician = Technician::factory()->create(['user_id' => $user->id]);
        ServiceAssignment::factory()->create([
            'service_request_id' => $request->id,
            'technician_id' => $technician->id,
        ]);

        $invoice = Invoice::factory()->create([
            'service_request_id' => $request->id,
            'customer_id' => $request->customer_id,
            'total' => $total,
            'status' => InvoiceStatus::PendingPayment->value,
            'paid_at' => null,
        ]);

        return [
            'invoice' => $invoice,
            'request' => $request,
        ];
    }

    private function pngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, is_string($png) ? $png : '');
    }
}
