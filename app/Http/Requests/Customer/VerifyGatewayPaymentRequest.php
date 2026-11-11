<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class VerifyGatewayPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'Authority' => ['required', 'string', 'max:255'],
            'Status' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function authority(): string
    {
        return (string) $this->validated('Authority');
    }

    public function gatewayStatusOk(): bool
    {
        $status = strtoupper((string) $this->input('Status', ''));

        return $status === '' || $status === 'OK';
    }
}
