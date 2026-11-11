<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Events\InvoiceCreated;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Services\DocumentNumberService;
use App\Services\InvoiceService;
use App\Support\DocumentType;
use App\Support\InvoiceStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CreateInvoice
{
    public function __construct(
        private readonly DocumentNumberService $documentNumbers,
        private readonly InvoiceService $invoices,
    ) {}

    /**
     * @param  array{discount: string, tax: string}  $attributes
     */
    public function execute(ServiceRequest $serviceRequest, array $attributes, int $year): Invoice
    {
        $parts = $this->invoices->reportParts($serviceRequest);

        if ($parts->isEmpty()) {
            throw new InvalidArgumentException(
                'An invoice requires service report parts on this service request.',
            );
        }

        $serviceRequest->loadMissing(['customer', 'address']);

        $customer = $serviceRequest->customer;
        if ($customer === null) {
            throw new InvalidArgumentException('The service request has no customer.');
        }

        $items = [];
        $lineTotals = [];

        foreach ($parts as $part) {
            $item = $this->invoices->itemFromPart($part);
            $items[] = $item;
            $lineTotals[] = $item['total'];
        }

        $discount = $this->invoices->money($attributes['discount']);
        $tax = $this->invoices->money($attributes['tax']);
        $subtotal = $this->invoices->subtotal($lineTotals);
        $total = $this->invoices->invoiceTotal($subtotal, $discount, $tax);
        $snapshot = $this->invoices->customerSnapshot($customer, $serviceRequest->address);

        $invoice = DB::transaction(function () use (
            $serviceRequest,
            $year,
            $snapshot,
            $items,
            $subtotal,
            $discount,
            $tax,
            $total,
        ): Invoice {
            $invoice = Invoice::query()->create([
                'invoice_number' => $this->documentNumbers->nextNumber(DocumentType::Invoice->value, $year),
                'service_request_id' => $serviceRequest->id,
                'customer_id' => $serviceRequest->customer_id,
                'customer_name' => $snapshot['customer_name'],
                'customer_mobile' => $snapshot['customer_mobile'],
                'customer_address' => $snapshot['customer_address'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'status' => InvoiceStatus::PendingPayment->value,
                'issued_at' => now(),
                'paid_at' => null,
            ]);

            foreach ($items as $item) {
                $invoice->items()->create($item);
            }

            return $invoice;
        });

        InvoiceCreated::dispatch((int) $invoice->id);

        return $invoice;
    }
}
