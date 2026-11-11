<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user !== null && $user->hasRole(RoleName::CompanyAdmin);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:30'],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['required', 'string', 'max:20', 'in:active,inactive'],
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     province: string,
     *     city: string,
     *     address: string,
     *     phone: string,
     *     is_default: bool,
     *     status: string
     * }
     */
    public function catalogPayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'province' => $validated['province'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'status' => $validated['status'],
        ];
    }
}
