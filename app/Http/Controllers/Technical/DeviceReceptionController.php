<?php

declare(strict_types=1);

namespace App\Http\Controllers\Technical;

use App\Actions\Workshop\DeliverDevice;
use App\Actions\Workshop\ReceiveDevice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Technical\DeliverDeviceRequest;
use App\Http\Requests\Technical\ReceiveDeviceRequest;
use App\Models\DeviceReception;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\WorkshopService;
use App\Support\ReceptionReceipt;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeviceReceptionController extends Controller
{
    use AuthorizesRequests;

    public function store(
        ReceiveDeviceRequest $request,
        ServiceRequest $serviceRequest,
        ReceiveDevice $receiveDevice,
    ): RedirectResponse {
        $user = $request->user('web');
        if (! $user instanceof User) {
            abort(403);
        }

        $receiveDevice->execute(
            $serviceRequest,
            $user,
            $request->receptionAttributes(),
            (int) config('document.year'),
        );

        return redirect()
            ->route('technical.service-requests.show', $serviceRequest)
            ->with('status', 'Device received.');
    }

    public function deliver(
        DeliverDeviceRequest $request,
        ServiceRequest $serviceRequest,
        DeliverDevice $deliverDevice,
    ): RedirectResponse {
        $user = $request->user('web');
        if (! $user instanceof User) {
            abort(403);
        }

        $deliverDevice->execute(
            $serviceRequest,
            $user,
            $request->deliveryAttributes(),
        );

        return redirect()
            ->route('technical.service-requests.show', $serviceRequest)
            ->with('status', 'Device delivered.');
    }

    public function pdf(ServiceRequest $serviceRequest, WorkshopService $workshop): StreamedResponse
    {
        $this->authorize('view', $serviceRequest);

        $reception = $serviceRequest->reception;
        if (! $reception instanceof DeviceReception) {
            abort(404);
        }

        $path = $workshop->pdfRelativePath($reception);
        if (! Storage::disk(ReceptionReceipt::DISK)->exists($path)) {
            $workshop->generatePdf($reception);
        }

        return Storage::disk(ReceptionReceipt::DISK)->response($path, $reception->receipt_number.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
