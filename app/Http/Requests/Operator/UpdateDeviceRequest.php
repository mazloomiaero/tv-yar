<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use App\Http\Requests\Operator\Concerns\ValidatesDeviceCustomFieldValues;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Device;
use App\Models\DeviceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDeviceRequest extends FormRequest
{
    use ValidatesDeviceCustomFieldValues;

    public function authorize(): bool
    {
        $user = $this->user('web');
        $customer = $this->route('customer');
        $device = $this->route('device');

        return $user !== null
            && $customer instanceof Customer
            && $device instanceof Device
            && $user->can('update', $device);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'device_type_id' => ['required', 'integer', Rule::exists(DeviceType::class, 'id')->whereNull('deleted_at')],
            'brand_id' => ['required', 'integer', Rule::exists(Brand::class, 'id')->whereNull('deleted_at')],
            'model' => ['required', 'string', 'max:150'],
            'serial_number' => ['required', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:150'],
            'purchase_date' => ['required', 'date'],
            'warranty_status' => ['required', 'string', 'max:30'],
            'warranty_until' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'custom_field_values' => ['sometimes', 'array'],
            'custom_field_values.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateMappedCustomFieldValues($validator);
        });
    }
}
