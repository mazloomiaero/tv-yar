<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user !== null && $user->can('create', Customer::class);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_code' => ['required', 'string', 'max:30', Rule::unique('customers', 'customer_code')],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile' => [
                'required',
                'string',
                'size:11',
                'regex:/^09\d{9}$/',
                Rule::unique('customers', 'mobile')->whereNull('deleted_at'),
            ],
            'mobile_secondary' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'max:150'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:20'],
            'portal_username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')],
            'portal_password' => ['nullable', 'required_with:portal_username', 'string', 'min:4', 'confirmed'],
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
