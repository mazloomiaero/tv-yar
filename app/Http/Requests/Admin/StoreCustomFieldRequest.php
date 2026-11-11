<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomFieldRequest extends FormRequest
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
            'option_names' => ['nullable', 'string'],
            'device_type_ids' => ['sometimes', 'array'],
            'device_type_ids.*' => ['integer', Rule::exists('device_types', 'id')->whereNull('deleted_at')],
            'service_type_ids' => ['sometimes', 'array'],
            'service_type_ids.*' => ['integer', Rule::exists('service_types', 'id')->whereNull('deleted_at')],
        ];
    }

    /**
     * @return array{name: string, option_names: list<string>, device_type_ids: list<int>, service_type_ids: list<int>}
     */
    public function catalogPayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'option_names' => $this->parseOptionNames($validated['option_names'] ?? null),
            'device_type_ids' => array_map('intval', $validated['device_type_ids'] ?? []),
            'service_type_ids' => array_map('intval', $validated['service_type_ids'] ?? []),
        ];
    }

    /**
     * @return list<string>
     */
    private function parseOptionNames(mixed $raw): array
    {
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw);
        if ($lines === false) {
            return [];
        }

        $names = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $names[] = $trimmed;
            }
        }

        return $names;
    }
}
