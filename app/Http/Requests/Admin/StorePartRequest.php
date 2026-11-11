<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class StorePartRequest extends FormRequest
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
            'code' => ['nullable', 'string', 'max:50'],
            'default_unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'status' => ['required', 'string', 'max:20'],
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     code: string|null,
     *     default_unit_price: string,
     *     unit: string,
     *     status: string
     * }
     */
    public function catalogPayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'default_unit_price' => $validated['default_unit_price'],
            'unit' => $validated['unit'],
            'status' => $validated['status'],
        ];
    }
}
