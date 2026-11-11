<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Device;
use App\Models\DeviceReception;
use App\Models\Feedback;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceAssignment;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\Technician;
use App\Support\InvoiceStatus;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use App\Support\ServiceRequestSource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-only aggregates of committed rows. Does not suggest technicians,
 * verify gateways, or write custom field values.
 */
class ReportService
{
    /**
     * Row counts of existing entities (not product KPIs).
     *
     * @return array{
     *     customers: int,
     *     devices: int,
     *     service_requests: int,
     *     service_assignments: int,
     *     service_reports: int,
     *     device_receptions: int,
     *     invoices: int,
     *     payments: int,
     *     feedbacks: int,
     *     technicians: int,
     *     branches: int
     * }
     */
    public function entityCounts(): array
    {
        return [
            'customers' => Customer::query()->count(),
            'devices' => Device::query()->count(),
            'service_requests' => ServiceRequest::query()->count(),
            'service_assignments' => ServiceAssignment::query()->count(),
            'service_reports' => ServiceReport::query()->count(),
            'device_receptions' => DeviceReception::query()->count(),
            'invoices' => Invoice::query()->count(),
            'payments' => Payment::query()->count(),
            'feedbacks' => Feedback::query()->count(),
            'technicians' => Technician::query()->count(),
            'branches' => Branch::query()->count(),
        ];
    }

    /**
     * Counts of service_requests.source (website, phone, walk-in).
     *
     * @return array<string, int>
     */
    public function requestCountsBySource(): array
    {
        return $this->groupedCounts('service_requests', 'source', ServiceRequestSource::values());
    }

    /**
     * Counts of service_assignments.is_suggested (reporting purpose of the column).
     *
     * @return array{total: int, suggested: int, not_suggested: int}
     */
    public function assignmentSuggestionCounts(): array
    {
        $total = ServiceAssignment::query()->count();
        $suggested = ServiceAssignment::query()->where('is_suggested', true)->count();

        return [
            'total' => $total,
            'suggested' => $suggested,
            'not_suggested' => $total - $suggested,
        ];
    }

    /**
     * Counts of invoices.status (pending_payment, partially_paid, paid, cancelled).
     *
     * @return array<string, int>
     */
    public function invoiceCountsByStatus(): array
    {
        return $this->groupedCounts(
            'invoices',
            'status',
            array_column(InvoiceStatus::cases(), 'value'),
        );
    }

    /**
     * Counts of payments.method (gateway, card_transfer, cash).
     *
     * @return array<string, int>
     */
    public function paymentCountsByMethod(): array
    {
        return $this->groupedCounts('payments', 'method', PaymentMethod::values());
    }

    /**
     * Counts of payments.status (pending_confirmation and stored gateway statuses).
     *
     * @return array<string, int>
     */
    public function paymentCountsByStatus(): array
    {
        return $this->groupedCounts('payments', 'status', array_column(PaymentStatus::cases(), 'value'));
    }

    /**
     * Invoice and payment field aggregates. Amounts are SUM of stored columns.
     *
     * @return array{
     *     invoices_by_status: array<string, array{count: int, amount: string}>,
     *     payments_by_method: array<string, array{count: int, amount: string}>,
     *     payments_by_status: array<string, array{count: int, amount: string}>
     * }
     */
    public function financialReport(): array
    {
        return [
            'invoices_by_status' => $this->groupedSums(
                'invoices',
                'status',
                'total',
                array_column(InvoiceStatus::cases(), 'value'),
            ),
            'payments_by_method' => $this->groupedSums(
                'payments',
                'method',
                'amount',
                PaymentMethod::values(),
            ),
            'payments_by_status' => $this->groupedSums(
                'payments',
                'status',
                'amount',
                array_column(PaymentStatus::cases(), 'value'),
            ),
        ];
    }

    /**
     * @return Collection<int, ServiceRequest>
     */
    public function serviceRequests(): Collection
    {
        return ServiceRequest::query()
            ->with('status')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function invoices(): Collection
    {
        return Invoice::query()
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, Payment>
     */
    public function payments(): Collection
    {
        return Payment::query()
            ->with('invoice')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, ServiceAssignment>
     */
    public function assignmentsForTechnician(?Technician $technician): Collection
    {
        if (! $technician instanceof Technician) {
            return ServiceAssignment::query()->whereKey([])->get();
        }

        return ServiceAssignment::query()
            ->with(['serviceRequest.status', 'branch'])
            ->where('technician_id', $technician->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, ServiceRequest>
     */
    public function serviceRequestsForCustomer(Customer $customer): Collection
    {
        return $customer->serviceRequests()
            ->with(['status', 'reception'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function invoicesForCustomer(Customer $customer): Collection
    {
        return $customer->invoices()
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, int>
     */
    private function groupedCounts(string $table, string $column, array $keys): array
    {
        $found = DB::table($table)
            ->select($column, DB::raw('COUNT(*) as aggregate'))
            ->groupBy($column)
            ->pluck('aggregate', $column)
            ->all();

        $counts = [];
        foreach ($keys as $key) {
            $counts[$key] = (int) ($found[$key] ?? 0);
        }

        return $counts;
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, array{count: int, amount: string}>
     */
    private function groupedSums(string $table, string $column, string $sumColumn, array $keys): array
    {
        $rows = DB::table($table)
            ->select($column, DB::raw('COUNT(*) as row_count'), DB::raw('SUM('.$sumColumn.') as amount_sum'))
            ->groupBy($column)
            ->get()
            ->keyBy($column);

        $groups = [];
        foreach ($keys as $key) {
            $row = $rows->get($key);
            $groups[$key] = [
                'count' => (int) ($row->row_count ?? 0),
                'amount' => $this->money((string) ($row->amount_sum ?? '0')),
            ];
        }

        return $groups;
    }

    private function money(string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
