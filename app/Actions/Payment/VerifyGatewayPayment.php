<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Events\PaymentConfirmed;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Services\Payment\GatewayInterface;
use App\Services\PaymentService;
use App\Support\PaymentStatus;
use App\Support\PaymentTransactionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class VerifyGatewayPayment
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly GatewayInterface $gateway,
    ) {}

    public function execute(string $authority): Payment
    {
        $transaction = PaymentTransaction::query()
            ->where('authority', $authority)
            ->first();

        if (! $transaction instanceof PaymentTransaction) {
            throw new InvalidArgumentException('Gateway transaction was not found.');
        }

        $payment = $transaction->payment;
        if (! $payment instanceof Payment) {
            throw new InvalidArgumentException('Gateway payment was not found.');
        }

        if ($payment->status === PaymentStatus::Completed->value) {
            return $payment;
        }

        $result = $this->gateway->verify($authority, (string) $payment->amount);

        if (! $result['ok']) {
            Log::notice('Gateway verify did not confirm payment.', [
                'payment_id' => $payment->id,
                'gateway_code' => $result['response_payload']['code'] ?? null,
            ]);
        }

        $dispatched = false;

        $verified = DB::transaction(function () use ($payment, $transaction, $result, &$dispatched): Payment {
            /** @var Payment $locked */
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            /** @var PaymentTransaction $lockedTx */
            $lockedTx = PaymentTransaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === PaymentStatus::Completed->value) {
                return $locked;
            }

            $response = $result['response_payload'];
            $lockedTx->update([
                'transaction_id' => $result['transaction_id'],
                'reference_id' => $result['reference_id'],
                'response_data' => $response,
                'status' => $result['ok']
                    ? PaymentTransactionStatus::Verified->value
                    : PaymentTransactionStatus::Failed->value,
            ]);

            if (! $result['ok']) {
                return $locked->fresh() ?? $locked;
            }

            $reference = $result['reference_id'] !== ''
                ? $result['reference_id']
                : $locked->reference_number;

            $locked->update([
                'status' => PaymentStatus::Completed->value,
                'paid_at' => now(),
                'reference_number' => $this->limitReference($reference),
            ]);

            /** @var Invoice $invoice */
            $invoice = Invoice::query()->whereKey($locked->invoice_id)->lockForUpdate()->firstOrFail();
            $this->payments->settleInvoice($invoice);

            $dispatched = true;

            return $locked->fresh() ?? $locked;
        });

        if ($dispatched) {
            PaymentConfirmed::dispatch((int) $verified->id);
        }

        return $verified;
    }

    private function limitReference(string $value): string
    {
        if (mb_strlen($value) <= 150) {
            return $value;
        }

        return mb_substr($value, 0, 150);
    }
}
