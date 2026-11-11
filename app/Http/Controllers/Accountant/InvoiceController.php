<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accountant;

use App\Actions\Invoice\CreateInvoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accountant\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Services\InvoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->orderByDesc('id')
            ->get();

        $billableRequests = ServiceRequest::query()
            ->whereHas('reports.parts')
            ->orderByDesc('id')
            ->get();

        return view('accountant.invoices.index', [
            'title' => 'Invoices',
            'invoices' => $invoices,
            'billableRequests' => $billableRequests,
        ]);
    }

    public function create(ServiceRequest $serviceRequest, InvoiceService $invoices): View
    {
        $this->authorize('create', [Invoice::class, $serviceRequest]);

        $serviceRequest->load(['customer', 'address']);
        $parts = $invoices->reportParts($serviceRequest);
        $preview = $parts->map(fn ($part) => $invoices->itemFromPart($part))->all();
        $lineTotals = array_map(fn (array $item): string => $item['total'], $preview);

        return view('accountant.invoices.create', [
            'title' => 'Create invoice',
            'serviceRequest' => $serviceRequest,
            'preview' => $preview,
            'subtotal' => $invoices->subtotal($lineTotals),
        ]);
    }

    public function store(
        StoreInvoiceRequest $request,
        ServiceRequest $serviceRequest,
        CreateInvoice $createInvoice,
    ): RedirectResponse {
        $invoice = $createInvoice->execute(
            $serviceRequest,
            $request->invoiceAttributes(),
            (int) config('document.year'),
        );

        return redirect()
            ->route('accountant.invoices.show', $invoice)
            ->with('status', 'Invoice created.');
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['items', 'serviceRequest', 'payments']);

        return view('accountant.invoices.show', [
            'title' => 'Invoice '.$invoice->invoice_number,
            'invoice' => $invoice,
        ]);
    }
}
