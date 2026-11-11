<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\PaymentConfirmed;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\InvoiceStatus;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function money(string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    public function completedAmount(Invoice $invoice): string
    {
        $sum = (string) $invoice->payments()
            ->where('status', PaymentStatus::Completed->value)
            ->sum('amount');

        return $this->money($sum);
    }

    public function remainingAmount(Invoice $invoice): string
    {
        $remaining = (float) $invoice->total - (float) $this->completedAmount($invoice);

        if ($remaining < 0) {
            $remaining = 0;
        }

        return $this->money((string) $remaining);
    }

    public function invoiceIsPayable(Invoice $invoice): bool
    {
        if ($invoice->status === InvoiceStatus::Cancelled->value) {
            return false;
        }

        if ($invoice->status === InvoiceStatus::Paid->value) {
            return false;
        }

        return (float) $this->remainingAmount($invoice) > 0;
    }

    public function reference(string $prefix): string
    {
        return $prefix.'-'.strtoupper(bin2hex(random_bytes(6)));
    }

    public function settleInvoice(Invoice $invoice): void
    {
        if ($invoice->status === InvoiceStatus::Cancelled->value) {
            return;
        }

        $completed = (float) $this->completedAmount($invoice);
        $total = (float) $invoice->total;

        if ($completed <= 0) {
            $invoice->update([
                'status' => InvoiceStatus::PendingPayment->value,
                'paid_at' => null,
            ]);

            return;
        }

        if ($completed + 0.00001 >= $total) {
            $invoice->update([
                'status' => InvoiceStatus::Paid->value,
                'paid_at' => $invoice->paid_at ?? now(),
            ]);

            return;
        }

        $invoice->update([
            'status' => InvoiceStatus::PartiallyPaid->value,
            'paid_at' => null,
        ]);
    }

    /**
     * Accountant confirmation for card_transfer/cash. Not a new payment method.
     *
     * @return array{payment: Payment, alreadyConfirmed: bool}
     */
    public function confirmManual(Payment $payment): array
    {
        if ($payment->method === PaymentMethod::Gateway->value) {
            throw new InvalidArgumentException('Gateway payments are confirmed by verify, not accountant confirmation.');
        }

        $dispatched = false;
        $alreadyConfirmed = false;

        $confirmed = DB::transaction(function () use ($payment, &$dispatched, &$alreadyConfirmed): Payment {
            /** @var Payment $locked */
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === PaymentStatus::Completed->value) {
                $alreadyConfirmed = true;

                return $locked;
            }

            if ($locked->status !== PaymentStatus::PendingConfirmation->value) {
                throw new InvalidArgumentException('Only pending manual payments can be confirmed.');
            }

            $locked->update([
                'status' => PaymentStatus::Completed->value,
                'paid_at' => now(),
            ]);

            /** @var Invoice $invoice */
            $invoice = Invoice::query()->whereKey($locked->invoice_id)->lockForUpdate()->firstOrFail();
            $this->settleInvoice($invoice);

            $dispatched = true;

            return $locked->fresh() ?? $locked;
        });

        if ($dispatched) {
            PaymentConfirmed::dispatch((int) $confirmed->id);
        }

        return [
            'payment' => $confirmed,
            'alreadyConfirmed' => $alreadyConfirmed,
        ];
    }
}
