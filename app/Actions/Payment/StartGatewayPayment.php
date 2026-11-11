<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Events\PaymentInitiated;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\GatewayInterface;
use App\Services\PaymentService;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use App\Support\PaymentTransactionStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class StartGatewayPayment
{
    public function __construct(
        private readonly CreatePayment $createPayment,
        private readonly PaymentService $payments,
        private readonly GatewayInterface $gateway,
    ) {}

    /**
     * @return array{payment: Payment, payment_url: string}
     */
    public function execute(Invoice $invoice, ?string $amount = null): array
    {
        $started = DB::transaction(function () use ($invoice, $amount): array {
            /** @var Invoice $locked */
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if (! $this->payments->invoiceIsPayable($locked)) {
                throw new InvalidArgumentException('This invoice cannot accept a gateway payment.');
            }

            $remaining = $this->payments->remainingAmount($locked);
            $payAmount = $this->payments->money($amount ?? $remaining);

            if ((float) $payAmount <= 0) {
                throw new InvalidArgumentException('Payment amount must be greater than zero.');
            }

            if ((float) $payAmount - (float) $remaining > 0.00001) {
                throw new InvalidArgumentException('Payment amount exceeds the invoice remaining balance.');
            }

            $callbackUrl = route('customer.payments.callback');
            $description = 'Invoice '.$locked->invoice_number;
            $gatewayStart = $this->gateway->start($payAmount, $callbackUrl, $description);

            $payment = $this->createPayment->execute([
                'invoice_id' => (int) $locked->id,
                'amount' => $payAmount,
                'method' => PaymentMethod::Gateway->value,
                'status' => PaymentStatus::Initiated->value,
                'paid_at' => null,
                'reference_number' => $this->payments->reference('GW'),
                'recorded_by' => null,
                'receipt_image' => null,
            ]);

            $payment->transaction()->create([
                'gateway' => 'zarinpal',
                'authority' => $gatewayStart['authority'],
                'transaction_id' => '',
                'reference_id' => '',
                'request_data' => $gatewayStart['request_payload'],
                'response_data' => $gatewayStart['response_payload'],
                'status' => PaymentTransactionStatus::Initiated->value,
            ]);

            return [
                'payment' => $payment->fresh(['transaction']) ?? $payment,
                'payment_url' => $gatewayStart['payment_url'],
            ];
        });

        PaymentInitiated::dispatch((int) $started['payment']->id);

        return $started;
    }
}
