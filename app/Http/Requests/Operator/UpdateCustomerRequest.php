<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $customer = $this->route('customer');

        return $user !== null
            && $customer instanceof Customer
            && $user->can('update', $customer);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        /** @var Customer $customer */
        $customer = $this->route('customer');

        return [
            'customer_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('customers', 'customer_code')->ignore($customer->id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile' => [
                'required',
                'string',
                'size:11',
                'regex:/^09\d{9}$/',
                Rule::unique('customers', 'mobile')->ignore($customer->id)->whereNull('deleted_at'),
            ],
            'mobile_secondary' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'max:150'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:20'],
            'portal_username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($customer->user_id),
                Rule::requiredIf(fn (): bool => $customer->user_id === null && $this->filled('portal_password')),
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.size' => 'تلفن همراه باید دقیقاً ۱۱ رقم باشد.',
            'mobile.regex' => 'تلفن همراه باید عدد باشد و با ۰۹ شروع شود.',
        ];
    }
}
