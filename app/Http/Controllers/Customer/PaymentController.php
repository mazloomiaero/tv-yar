<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Actions\Payment\StartGatewayPayment;
use App\Actions\Payment\VerifyGatewayPayment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StartGatewayPaymentRequest;
use App\Http\Requests\Customer\VerifyGatewayPaymentRequest;
use App\Jobs\VerifyPaymentJob;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class PaymentController extends Controller
{
    public function store(
        StartGatewayPaymentRequest $request,
        Invoice $invoice,
        StartGatewayPayment $startGatewayPayment,
    ): RedirectResponse {
        $this->ownInvoice($request->user('customer'), $invoice);

        $started = $startGatewayPayment->execute($invoice, $request->amount());

        return redirect()->away($started['payment_url']);
    }

    public function callback(
        VerifyGatewayPaymentRequest $request,
        VerifyGatewayPayment $verifyGatewayPayment,
    ): RedirectResponse {
        $transaction = PaymentTransaction::query()
            ->where('authority', $request->authority())
            ->first();

        $payment = $transaction?->payment;
        if (! $payment instanceof Payment) {
            abort(404);
        }

        $invoice = $payment->invoice;
        if (! $invoice instanceof Invoice) {
            abort(404);
        }

        if (! $request->gatewayStatusOk()) {
            return $this->afterCallbackRedirect(
                $request->user('customer'),
                $invoice,
                'Gateway redirect is not payment success. Verify was not run.',
            );
        }

        try {
            $verifyGatewayPayment->execute($request->authority());
        } catch (InvalidArgumentException $exception) {
            Log::notice('Gateway verify rejected.', [
                'payment_id' => $payment->id,
                'reason' => $exception->getMessage(),
            ]);

            return $this->afterCallbackRedirect(
                $request->user('customer'),
                $invoice,
                $exception->getMessage(),
            );
        } catch (RuntimeException $exception) {
            Log::error('Gateway verify failed.', [
                'payment_id' => $payment->id,
            ]);

            VerifyPaymentJob::dispatch($request->authority());

            return $this->afterCallbackRedirect(
                $request->user('customer'),
                $invoice,
                'Payment verification could not be completed yet.',
            );
        }

        return $this->afterCallbackRedirect(
            $request->user('customer'),
            $invoice->fresh() ?? $invoice,
            'Payment verified.',
        );
    }

    private function afterCallbackRedirect(mixed $customer, Invoice $invoice, string $status): RedirectResponse
    {
        if ($customer instanceof Customer && $invoice->customer_id === $customer->id) {
            return redirect()
                ->route('customer.invoices.show', $invoice)
                ->with('status', $status);
        }

        if ($customer instanceof Customer) {
            return redirect()
                ->route('customer.invoices.index')
                ->with('status', $status);
        }

        return redirect()
            ->route('customer.login')
            ->with('status', $status);
    }

    private function ownInvoice(mixed $customer, ?Invoice $invoice): Invoice
    {
        if (! $customer instanceof Customer || ! $invoice instanceof Invoice || $invoice->customer_id !== $customer->id) {
            abort(404);
        }

        return $invoice;
    }
}
