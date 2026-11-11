<?php

declare(strict_types=1);

namespace App\Http\Requests\Accountant;

use App\Models\Payment;
use App\Models\User;
use App\Support\PaymentMethod;
use App\Support\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ConfirmManualPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $payment = $this->route('payment');

        return $user instanceof User
            && $payment instanceof Payment
            && $user->can('verify', $payment);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $payment = $this->route('payment');
            if (! $payment instanceof Payment) {
                return;
            }

            if ($payment->method === PaymentMethod::Gateway->value) {
                $validator->errors()->add(
                    'payment',
                    'Gateway payments are confirmed by verify, not accountant confirmation.',
                );
            }

            if (
                $payment->status !== PaymentStatus::PendingConfirmation->value
                && $payment->status !== PaymentStatus::Completed->value
            ) {
                $validator->errors()->add('payment', 'Only pending manual payments can be confirmed.');
            }
        });
    }
}
