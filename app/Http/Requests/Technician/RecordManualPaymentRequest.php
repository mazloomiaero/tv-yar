<?php

declare(strict_types=1);

namespace App\Http\Requests\Technician;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use App\Support\PaymentMethod;
use App\Support\PaymentReceiptRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RecordManualPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $invoice = $this->route('invoice');

        return $user instanceof User
            && $invoice instanceof Invoice
            && $user->can('create', [Payment::class, $invoice]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $imageRule = [
            'nullable',
            'file',
            'image',
            'mimes:'.implode(',', PaymentReceiptRules::extensions()),
            'max:'.PaymentReceiptRules::MAX_KILOBYTES,
        ];

        return [
            'method' => ['required', 'string', Rule::in(PaymentMethod::manualValues())],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'reference_number' => ['nullable', 'string', 'max:150'],
            'receipt_image' => $imageRule,
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $invoice = $this->route('invoice');
            if (! $invoice instanceof Invoice) {
                return;
            }

            $method = (string) $this->input('method');

            if ($method === PaymentMethod::CardTransfer->value) {
                if (! $this->file('receipt_image') instanceof UploadedFile) {
                    $validator->errors()->add('receipt_image', 'A receipt image is required for card transfer.');
                }

                if (trim((string) $this->input('reference_number')) === '') {
                    $validator->errors()->add('reference_number', 'A reference number is required for card transfer.');
                }
            }

            $payments = $this->container->make(PaymentService::class);

            if (! $payments->invoiceIsPayable($invoice)) {
                $validator->errors()->add('amount', 'This invoice cannot accept a manual payment.');

                return;
            }

            $remaining = $payments->remainingAmount($invoice);
            $amount = $payments->money((string) ($this->input('amount') ?? '0'));

            if ((float) $amount - (float) $remaining > 0.00001) {
                $validator->errors()->add('amount', 'Payment amount exceeds the invoice remaining balance.');
            }
        });
    }

    /**
     * @return array{method: string, amount: string, reference_number: string|null}
     */
    public function paymentAttributes(): array
    {
        $validated = $this->validated();

        return [
            'method' => (string) $validated['method'],
            'amount' => (string) $validated['amount'],
            'reference_number' => isset($validated['reference_number']) && $validated['reference_number'] !== ''
                ? (string) $validated['reference_number']
                : null,
        ];
    }

    public function receiptFile(): ?UploadedFile
    {
        $file = $this->file('receipt_image');

        return $file instanceof UploadedFile ? $file : null;
    }
}
