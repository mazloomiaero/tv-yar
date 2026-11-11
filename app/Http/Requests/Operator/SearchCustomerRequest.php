<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class SearchCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user !== null && $user->can('viewAny', Customer::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:150'],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }
}
