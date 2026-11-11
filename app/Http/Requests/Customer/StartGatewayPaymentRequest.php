<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\PaymentService;
use App\Support\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StartGatewayPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->user('customer');
        $invoice = $this->route('invoice');

        if (! $customer instanceof Customer || ! $invoice instanceof Invoice) {
            return false;
        }

        if ($invoice->customer_id !== $customer->id) {
            abort(404);
        }

        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['nullable', 'numeric', 'decimal:0,2', 'gt:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $invoice = $this->route('invoice');
            if (! $invoice instanceof Invoice) {
                return;
            }

            $payments = $this->container->make(PaymentService::class);

            if ($invoice->status === InvoiceStatus::Cancelled->value) {
                $validator->errors()->add('amount', 'This invoice cannot accept a gateway payment.');

                return;
            }

            if (! $payments->invoiceIsPayable($invoice)) {
                $validator->errors()->add('amount', 'This invoice cannot accept a gateway payment.');

                return;
            }

            $remaining = $payments->remainingAmount($invoice);
            $amount = $payments->money((string) ($this->input('amount') ?? $remaining));

            if ((float) $amount - (float) $remaining > 0.00001) {
                $validator->errors()->add('amount', 'Payment amount exceeds the invoice remaining balance.');
            }
        });
    }

    public function amount(): ?string
    {
        $validated = $this->validated();

        if (! isset($validated['amount']) || $validated['amount'] === null || $validated['amount'] === '') {
            return null;
        }

        return (string) $validated['amount'];
    }
}
