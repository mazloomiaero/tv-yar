<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceAssignment;
use App\Models\ServiceRequest;
use App\Models\Technician;
use App\Support\InvoiceStatus;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use App\Support\RoleName;
use App\Support\ServiceRequestSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_dashboard_shows_source_and_invoice_status_counts(): void
    {
        ServiceRequest::factory()->create(['source' => ServiceRequestSource::Website->value]);
        Invoice::factory()->create(['status' => InvoiceStatus::Paid->value]);

        $admin = $this->staffUser(RoleName::CompanyAdmin);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('website: 1')
            ->assertSee('paid: 1')
            ->assertDontSee('SLA')
            ->assertDontSee('GPS');
    }

    public function test_operator_dashboard_lists_service_requests_and_feedback_count(): void
    {
        $request = ServiceRequest::factory()->create([
            'source' => ServiceRequestSource::Phone->value,
        ]);

        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('operator.dashboard'))
            ->assertOk()
            ->assertSee($request->request_number)
            ->assertSee('phone: 1')
            ->assertSee('feedbacks: 0');
    }

    public function test_technical_dashboard_shows_assignment_suggestion_counts(): void
    {
        ServiceAssignment::factory()->create(['is_suggested' => true]);

        $supervisor = $this->staffUser(RoleName::TechnicalSupervisor);

        $this->actingAs($supervisor)
            ->get(route('technical.dashboard'))
            ->assertOk()
            ->assertSee('is_suggested true: 1')
            ->assertSee('device_receptions: 0');
    }

    public function test_accountant_dashboard_shows_invoice_and_payment_fields(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => InvoiceStatus::PendingPayment->value,
            'total' => '12.00',
        ]);
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'method' => PaymentMethod::CardTransfer->value,
            'status' => PaymentStatus::PendingConfirmation->value,
            'amount' => '12.00',
        ]);

        $accountant = $this->staffUser(RoleName::Accountant);

        $this->actingAs($accountant)
            ->get(route('accountant.dashboard'))
            ->assertOk()
            ->assertSee($invoice->invoice_number)
            ->assertSee('pending_payment: 1')
            ->assertSee('card_transfer: 1')
            ->assertSee('pending_confirmation: 1')
            ->assertSee(route('accountant.financial-report', absolute: false));
    }

    public function test_technician_dashboard_lists_only_that_technician_assignments(): void
    {
        $user = $this->staffUser(RoleName::Technician);
        $mine = Technician::factory()->create(['user_id' => $user->id]);
        $other = Technician::factory()->create();
        $ownAssignment = ServiceAssignment::factory()->create([
            'technician_id' => $mine->id,
            'is_suggested' => true,
        ]);
        $otherAssignment = ServiceAssignment::factory()->create([
            'technician_id' => $other->id,
        ]);

        $this->actingAs($user)
            ->get(route('technician.dashboard'))
            ->assertOk()
            ->assertSee($ownAssignment->serviceRequest->request_number)
            ->assertDontSee($otherAssignment->serviceRequest->request_number)
            ->assertSee('is_suggested true: 1');
    }

    public function test_customer_dashboard_lists_own_requests_and_invoices(): void
    {
        $customer = Customer::factory()->create();
        $ownRequest = ServiceRequest::factory()->create(['customer_id' => $customer->id]);
        $ownInvoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'service_request_id' => $ownRequest->id,
        ]);
        $other = ServiceRequest::factory()->create();

        $this->actingAs($customer, 'customer')
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee($ownRequest->request_number)
            ->assertSee($ownInvoice->invoice_number)
            ->assertDontSee($other->request_number);
    }
}
