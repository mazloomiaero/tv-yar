<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DeviceReception;
use App\Support\PlainTextPdf;
use App\Support\ReceptionReceipt;
use Illuminate\Support\Facades\Storage;

class WorkshopService
{
    public function __construct(private readonly PlainTextPdf $pdf) {}

    public function pdfRelativePath(DeviceReception $reception): string
    {
        return ReceptionReceipt::relativePath($reception->receipt_number);
    }

    public function generatePdf(DeviceReception $reception): string
    {
        $path = $this->pdfRelativePath($reception);
        Storage::disk(ReceptionReceipt::DISK)->put($path, $this->pdf->render($this->receiptLines($reception)));

        return $path;
    }

    /**
     * @return list<string>
     */
    public function receiptLines(DeviceReception $reception): array
    {
        $reception->loadMissing(['serviceRequest', 'customer', 'device', 'receivedBy']);

        $customer = $reception->customer;
        $device = $reception->device;
        $request = $reception->serviceRequest;

        return [
            'Reception receipt '.$reception->receipt_number,
            'Request: '.($request?->request_number ?? ''),
            'Customer: '.trim(($customer?->customer_code ?? '').' '.($customer?->first_name ?? '').' '.($customer?->last_name ?? '')),
            'Device: '.trim(($device?->title ?? '').' '.($device?->serial_number ?? '')),
            'Received by: '.($reception->receivedBy?->username ?? ''),
            'Received date: '.($reception->received_date?->format('Y-m-d') ?? ''),
            'Appearance: '.$reception->appearance,
            'Accompanying items: '.$reception->accompanying_items,
            'Reason: '.$reception->reason,
            'Notes: '.$reception->notes,
            'Delivery date: '.($reception->delivery_date?->format('Y-m-d') ?? ''),
        ];
    }
}
