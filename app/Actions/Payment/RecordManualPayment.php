<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Events\PaymentInitiated;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use App\Support\PaymentMethod;
use App\Support\PaymentReceiptRules;
use App\Support\PaymentStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

final class RecordManualPayment
{
    public function __construct(
        private readonly CreatePayment $createPayment,
        private readonly PaymentService $payments,
    ) {}

    /**
     * @param  array{
     *     method: string,
     *     amount: string,
     *     reference_number?: string|null
     * }  $attributes
     */
    public function execute(Invoice $invoice, User $technicianUser, array $attributes, ?UploadedFile $receipt): Payment
    {
        $method = $attributes['method'];

        if (! in_array($method, PaymentMethod::manualValues(), true)) {
            throw new InvalidArgumentException('Manual payment method must be card_transfer or cash.');
        }

        $storedPath = null;

        try {
            $payment = DB::transaction(function () use ($invoice, $technicianUser, $attributes, $receipt, $method, &$storedPath): Payment {
                /** @var Invoice $locked */
                $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

                if (! $this->payments->invoiceIsPayable($locked)) {
                    throw new InvalidArgumentException('This invoice cannot accept a manual payment.');
                }

                $remaining = $this->payments->remainingAmount($locked);
                $amount = $this->payments->money($attributes['amount']);

                if ((float) $amount <= 0) {
                    throw new InvalidArgumentException('Payment amount must be greater than zero.');
                }

                if ((float) $amount - (float) $remaining > 0.00001) {
                    throw new InvalidArgumentException('Payment amount exceeds the invoice remaining balance.');
                }

                if ($method === PaymentMethod::CardTransfer->value) {
                    if (! $receipt instanceof UploadedFile) {
                        throw new InvalidArgumentException('A receipt image is required for card transfer.');
                    }

                    $reference = trim((string) ($attributes['reference_number'] ?? ''));
                    if ($reference === '') {
                        throw new InvalidArgumentException('A reference number is required for card transfer.');
                    }
                } else {
                    $reference = trim((string) ($attributes['reference_number'] ?? ''));
                    if ($reference === '') {
                        $reference = $this->payments->reference('CASH');
                    }
                }

                if ($receipt instanceof UploadedFile) {
                    $path = $receipt->store(PaymentReceiptRules::DIRECTORY, PaymentReceiptRules::DISK);
                    if (! is_string($path) || $path === '') {
                        throw new InvalidArgumentException('Unable to store the receipt image.');
                    }
                    $storedPath = $path;
                }

                return $this->createPayment->execute([
                    'invoice_id' => (int) $locked->id,
                    'amount' => $amount,
                    'method' => $method,
                    'status' => PaymentStatus::PendingConfirmation->value,
                    'paid_at' => null,
                    'reference_number' => $this->limitReference($reference),
                    'recorded_by' => (int) $technicianUser->id,
                    'receipt_image' => $storedPath,
                ]);
            });
        } catch (Throwable $exception) {
            if (is_string($storedPath) && $storedPath !== '') {
                Storage::disk(PaymentReceiptRules::DISK)->delete($storedPath);
            }

            throw $exception;
        }

        PaymentInitiated::dispatch((int) $payment->id);

        return $payment;
    }

    private function limitReference(string $value): string
    {
        if (mb_strlen($value) <= 150) {
            return $value;
        }

        return mb_substr($value, 0, 150);
    }
}
