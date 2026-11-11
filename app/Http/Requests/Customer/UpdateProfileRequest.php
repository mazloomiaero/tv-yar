<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('customer') instanceof Customer;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $customer = $this->user('customer');
        if (! $customer instanceof Customer) {
            return [];
        }

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'string', 'max:30'],
            'mobile_secondary' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'max:150'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'portal_username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($customer->user_id),
                Rule::requiredIf(fn (): bool => $customer->user_id !== null || $this->filled('portal_password')),
            ],
            'portal_password' => [
                'nullable',
                'string',
                'min:4',
                'confirmed',
                Rule::requiredIf(fn (): bool => $customer->user_id === null && $this->filled('portal_username')),
            ],
        ];
    }
}
