<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accountant\ConfirmManualPaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::query()
            ->with('invoice')
            ->orderByDesc('id')
            ->get();

        return view('accountant.payments.index', [
            'title' => 'Payments',
            'payments' => $payments,
        ]);
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['invoice', 'recordedBy', 'transaction']);

        return view('accountant.payments.show', [
            'title' => 'Payment '.$payment->reference_number,
            'payment' => $payment,
        ]);
    }

    public function confirm(
        ConfirmManualPaymentRequest $request,
        Payment $payment,
        PaymentService $payments,
    ): RedirectResponse {
        $payments->confirmManual($payment);

        return redirect()
            ->route('accountant.payments.show', $payment)
            ->with('status', 'Payment confirmed.');
    }
}
