<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Support\InvoiceStatus;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_accountant_reads_financial_report_without_mutating_rows(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => InvoiceStatus::PartiallyPaid->value,
            'total' => '20.00',
        ]);
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'method' => PaymentMethod::Cash->value,
            'status' => PaymentStatus::Completed->value,
            'amount' => '8.00',
        ]);

        $accountant = $this->staffUser(RoleName::Accountant);

        $this->actingAs($accountant)
            ->get(route('accountant.financial-report'))
            ->assertOk()
            ->assertSee('partially_paid: 1 — 20.00')
            ->assertSee('cash: 1 — 8.00')
            ->assertSee('completed: 1 — 8.00')
            ->assertDontSee('gateway verify');

        $this->assertSame(InvoiceStatus::PartiallyPaid->value, $invoice->fresh()->status);
        $this->assertSame(1, Invoice::query()->count());
        $this->assertSame(1, Payment::query()->count());
    }

    public function test_operator_cannot_open_financial_report(): void
    {
        $operator = $this->staffUser(RoleName::Operator);

        $this->actingAs($operator)
            ->get(route('accountant.financial-report'))
            ->assertForbidden();
    }
}
