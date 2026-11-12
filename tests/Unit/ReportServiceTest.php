<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceAssignment;
use App\Models\ServiceRequest;
use App\Services\ReportService;
use App\Support\InvoiceStatus;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use App\Support\ServiceRequestSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_counts_requests_by_documented_source(): void
    {
        ServiceRequest::factory()->create(['source' => ServiceRequestSource::Website->value]);
        ServiceRequest::factory()->create(['source' => ServiceRequestSource::Website->value]);
        ServiceRequest::factory()->create(['source' => ServiceRequestSource::Phone->value]);

        $counts = (new ReportService)->requestCountsBySource();

        $this->assertSame(2, $counts[ServiceRequestSource::Website->value]);
        $this->assertSame(1, $counts[ServiceRequestSource::Phone->value]);
        $this->assertSame(0, $counts[ServiceRequestSource::WalkIn->value]);
    }

    public function test_it_counts_assignment_suggestion_usage_from_is_suggested(): void
    {
        ServiceAssignment::factory()->create(['is_suggested' => true]);
        ServiceAssignment::factory()->create(['is_suggested' => true]);
        ServiceAssignment::factory()->create(['is_suggested' => false]);

        $counts = (new ReportService)->assignmentSuggestionCounts();

        $this->assertSame(3, $counts['total']);
        $this->assertSame(2, $counts['suggested']);
        $this->assertSame(1, $counts['not_suggested']);
    }

    public function test_financial_report_aggregates_invoice_status_and_payment_method_amounts(): void
    {
        $paid = Invoice::factory()->create([
            'status' => InvoiceStatus::Paid->value,
            'total' => '40.00',
        ]);
        $pending = Invoice::factory()->create([
            'status' => InvoiceStatus::PendingPayment->value,
            'total' => '10.50',
        ]);
        Payment::factory()->create([
            'invoice_id' => $paid->id,
            'method' => PaymentMethod::Cash->value,
            'status' => PaymentStatus::Completed->value,
            'amount' => '15.00',
        ]);
        Payment::factory()->create([
            'invoice_id' => $pending->id,
            'method' => PaymentMethod::Gateway->value,
            'status' => PaymentStatus::Initiated->value,
            'amount' => '5.25',
        ]);

        $report = (new ReportService)->financialReport();

        $this->assertSame(1, $report['invoices_by_status'][InvoiceStatus::Paid->value]['count']);
        $this->assertSame('40.00', $report['invoices_by_status'][InvoiceStatus::Paid->value]['amount']);
        $this->assertSame(1, $report['invoices_by_status'][InvoiceStatus::PendingPayment->value]['count']);
        $this->assertSame('10.50', $report['invoices_by_status'][InvoiceStatus::PendingPayment->value]['amount']);
        $this->assertSame(0, $report['invoices_by_status'][InvoiceStatus::Cancelled->value]['count']);
        $this->assertSame('0.00', $report['invoices_by_status'][InvoiceStatus::Cancelled->value]['amount']);

        $this->assertSame(1, $report['payments_by_method'][PaymentMethod::Cash->value]['count']);
        $this->assertSame('15.00', $report['payments_by_method'][PaymentMethod::Cash->value]['amount']);
        $this->assertSame(1, $report['payments_by_method'][PaymentMethod::Gateway->value]['count']);
        $this->assertSame('5.25', $report['payments_by_method'][PaymentMethod::Gateway->value]['amount']);
        $this->assertSame(0, $report['payments_by_method'][PaymentMethod::CardTransfer->value]['count']);

        $this->assertSame(1, $report['payments_by_status'][PaymentStatus::Completed->value]['count']);
        $this->assertSame(1, $report['payments_by_status'][PaymentStatus::Initiated->value]['count']);
        $this->assertSame(0, $report['payments_by_status'][PaymentStatus::PendingConfirmation->value]['count']);
    }

    public function test_entity_counts_read_existing_tables_only(): void
    {
        ServiceRequest::factory()->create();

        $counts = (new ReportService)->entityCounts();

        $this->assertSame(1, $counts['service_requests']);
        $this->assertSame(1, $counts['customers']);
        $this->assertSame(1, $counts['devices']);
        $this->assertArrayHasKey('invoices', $counts);
        $this->assertArrayHasKey('feedbacks', $counts);
        $this->assertArrayNotHasKey('work_orders', $counts);
        $this->assertArrayNotHasKey('tenant_id', $counts);
    }

    public function test_request_daily_counts_fills_calendar_days(): void
    {
        ServiceRequest::factory()->create();

        $series = (new ReportService)->requestDailyCounts(7);

        $this->assertCount(7, $series);
        $this->assertSame(now()->toDateString(), $series[6]['date']);
        $this->assertGreaterThanOrEqual(1, array_sum(array_column($series, 'count')));
    }
}
