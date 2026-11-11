<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $this->customer($request);

        $invoices = $customer->invoices()->orderByDesc('id')->get();

        return view('customer.invoices.index', [
            'title' => 'My invoices',
            'invoices' => $invoices,
        ]);
    }

    public function show(Request $request, Invoice $invoice): View
    {
        $invoice = $this->ownInvoice($request, $invoice);
        $invoice->load(['items', 'payments']);

        return view('customer.invoices.show', [
            'title' => 'Invoice '.$invoice->invoice_number,
            'invoice' => $invoice,
        ]);
    }

    private function ownInvoice(Request $request, Invoice $invoice): Invoice
    {
        $customer = $this->customer($request);

        if ($invoice->customer_id !== $customer->id) {
            abort(404);
        }

        return $invoice;
    }

    private function customer(Request $request): Customer
    {
        $customer = $request->user('customer');
        if (! $customer instanceof Customer) {
            abort(403);
        }

        return $customer;
    }
}
