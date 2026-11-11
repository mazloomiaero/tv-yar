<?php

declare(strict_types=1);

namespace App\Http\Requests\Accountant;

use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $serviceRequest = $this->route('serviceRequest');

        return $user instanceof User
            && $serviceRequest instanceof ServiceRequest
            && $user->can('create', [Invoice::class, $serviceRequest]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'discount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'tax' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceRequest = $this->route('serviceRequest');
            if (! $serviceRequest instanceof ServiceRequest) {
                return;
            }

            $invoices = $this->container->make(InvoiceService::class);
            $parts = $invoices->reportParts($serviceRequest);

            if ($parts->isEmpty()) {
                $validator->errors()->add(
                    'discount',
                    'An invoice requires service report parts on this service request.',
                );

                return;
            }

            $lineTotals = [];
            foreach ($parts as $part) {
                $lineTotals[] = $invoices->itemFromPart($part)['total'];
            }

            $discount = $invoices->money((string) ($this->input('discount') ?? '0'));
            $tax = $invoices->money((string) ($this->input('tax') ?? '0'));
            $subtotal = $invoices->subtotal($lineTotals);

            try {
                $invoices->invoiceTotal($subtotal, $discount, $tax);
            } catch (InvalidArgumentException $exception) {
                $validator->errors()->add('discount', $exception->getMessage());
            }
        });
    }

    /**
     * @return array{discount: string, tax: string}
     */
    public function invoiceAttributes(): array
    {
        $validated = $this->validated();

        return [
            'discount' => (string) ($validated['discount'] ?? '0'),
            'tax' => (string) ($validated['tax'] ?? '0'),
        ];
    }
}
