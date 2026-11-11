<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\ServiceType;
use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $serviceType = $this->route('service_type');

        return $user !== null
            && $user->hasRole(RoleName::CompanyAdmin)
            && $serviceType instanceof ServiceType;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'device_type_ids' => ['sometimes', 'array'],
            'device_type_ids.*' => ['integer', Rule::exists('device_types', 'id')->whereNull('deleted_at')],
            'service_status_ids' => ['sometimes', 'array'],
            'service_status_ids.*' => ['integer', Rule::exists('service_statuses', 'id')->whereNull('deleted_at')],
        ];
    }

    /**
     * @return array{name: string, device_type_ids: list<int>, service_status_ids: list<int>}
     */
    public function catalogPayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'device_type_ids' => array_map('intval', $validated['device_type_ids'] ?? []),
            'service_status_ids' => array_map('intval', $validated['service_status_ids'] ?? []),
        ];
    }
}
