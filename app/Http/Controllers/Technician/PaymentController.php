<?php

declare(strict_types=1);

namespace App\Http\Controllers\Technician;

use App\Actions\Payment\RecordManualPayment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\RecordManualPaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use App\Support\PaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function create(Invoice $invoice, PaymentService $payments): View
    {
        $this->authorize('create', [Payment::class, $invoice]);

        $invoice->load(['serviceRequest', 'payments']);

        return view('technician.payments.create', [
            'title' => 'Record payment',
            'invoice' => $invoice,
            'remaining' => $payments->remainingAmount($invoice),
            'methods' => PaymentMethod::manualValues(),
        ]);
    }

    public function store(
        RecordManualPaymentRequest $request,
        Invoice $invoice,
        RecordManualPayment $recordManualPayment,
    ): RedirectResponse {
        $user = $request->user('web');
        if (! $user instanceof User) {
            abort(403);
        }

        $payment = $recordManualPayment->execute(
            $invoice,
            $user,
            $request->paymentAttributes(),
            $request->receiptFile(),
        );

        return redirect()
            ->route('technician.payments.create', $invoice)
            ->with('status', 'Payment '.$payment->reference_number.' recorded and waiting for confirmation.');
    }
}
