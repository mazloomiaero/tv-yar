<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DeviceReception;
use App\Models\ServiceRequest;
use App\Services\WorkshopService;
use App\Support\ReceptionReceipt;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceptionController extends Controller
{
    public function show(Request $request, ServiceRequest $serviceRequest, WorkshopService $workshop): View
    {
        $reception = $this->ownReception($request, $serviceRequest);

        return view('customer.receptions.show', [
            'title' => 'Reception '.$reception->receipt_number,
            'reception' => $reception,
            'serviceRequest' => $serviceRequest,
            'lines' => $workshop->receiptLines($reception),
        ]);
    }

    public function pdf(Request $request, ServiceRequest $serviceRequest, WorkshopService $workshop): StreamedResponse
    {
        $reception = $this->ownReception($request, $serviceRequest);

        $path = $workshop->pdfRelativePath($reception);
        if (! Storage::disk(ReceptionReceipt::DISK)->exists($path)) {
            $workshop->generatePdf($reception);
        }

        return Storage::disk(ReceptionReceipt::DISK)->response($path, $reception->receipt_number.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function ownReception(Request $request, ServiceRequest $serviceRequest): DeviceReception
    {
        $customer = $request->user('customer');
        if (! $customer instanceof Customer || $serviceRequest->customer_id !== $customer->id) {
            abort(404);
        }

        $reception = $serviceRequest->reception;
        if (! $reception instanceof DeviceReception) {
            abort(404);
        }

        return $reception;
    }
}
