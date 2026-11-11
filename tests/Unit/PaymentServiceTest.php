<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Support\InvoiceStatus;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_amount_ignores_pending_manual_rows(): void
    {
        $service = new PaymentService;
        $invoice = Invoice::factory()->create(['total' => '100.00']);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => '40.00',
            'status' => PaymentStatus::PendingConfirmation->value,
        ]);
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => '25.00',
            'status' => PaymentStatus::Completed->value,
            'method' => PaymentMethod::Gateway->value,
        ]);

        $this->assertSame('25.00', $service->completedAmount($invoice));
        $this->assertSame('75.00', $service->remainingAmount($invoice));
    }

    public function test_settle_invoice_sets_partial_then_paid_without_inventing_statuses(): void
    {
        $service = new PaymentService;
        $invoice = Invoice::factory()->create([
            'total' => '100.00',
            'status' => InvoiceStatus::PendingPayment->value,
            'paid_at' => null,
        ]);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => '40.00',
            'status' => PaymentStatus::Completed->value,
        ]);

        $service->settleInvoice($invoice->fresh());
        $invoice = $invoice->fresh();
        $this->assertSame(InvoiceStatus::PartiallyPaid->value, $invoice->status);
        $this->assertNull($invoice->paid_at);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => '60.00',
            'status' => PaymentStatus::Completed->value,
        ]);

        $service->settleInvoice($invoice->fresh());
        $invoice = $invoice->fresh();
        $this->assertSame(InvoiceStatus::Paid->value, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }
}
