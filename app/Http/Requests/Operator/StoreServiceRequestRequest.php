<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use App\Http\Requests\Operator\Concerns\ValidatesServiceRequestCustomFieldValues;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Device;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Support\InitialServiceStatus;
use App\Support\ServiceRequestSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreServiceRequestRequest extends FormRequest
{
    use ValidatesServiceRequestCustomFieldValues;

    public function authorize(): bool
    {
        $user = $this->user('web');
        $customer = $this->route('customer');

        return $user !== null
            && $customer instanceof Customer
            && $user->can('create', ServiceRequest::class);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $customer = $this->route('customer');
        $customerId = $customer instanceof Customer ? $customer->id : 0;

        return [
            'device_id' => [
                'required',
                'integer',
                Rule::exists(Device::class, 'id')
                    ->where('customer_id', $customerId)
                    ->whereNull('deleted_at'),
            ],
            'service_type_id' => ['required', 'integer', Rule::exists(ServiceType::class, 'id')->whereNull('deleted_at')],
            'address_id' => [
                'required_without:create_address',
                'nullable',
                'integer',
                Rule::exists(CustomerAddress::class, 'id')
                    ->where('customer_id', $customerId)
                    ->whereNull('deleted_at'),
            ],
            'create_address' => ['sometimes', 'boolean'],
            'source' => ['required', 'string', Rule::in(ServiceRequestSource::values())],
            'priority' => ['required', 'string', 'max:20'],
            'problem_description' => ['required', 'string'],
            'preferred_date' => ['required', 'date'],
            'preferred_start_time' => ['required', 'date_format:H:i'],
            'preferred_end_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
            'custom_field_values' => ['sometimes', 'array'],
            'custom_field_values.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateDeviceServiceTypeLink($validator);
            $this->validateInitialStatus($validator);
            $this->validateMappedServiceRequestCustomFieldValues($validator);
        });
    }

    private function validateDeviceServiceTypeLink(Validator $validator): void
    {
        $deviceId = (int) $this->input('device_id');
        $serviceTypeId = (int) $this->input('service_type_id');
        if ($deviceId < 1 || $serviceTypeId < 1) {
            return;
        }

        $device = Device::query()->find($deviceId);
        if (! $device instanceof Device) {
            return;
        }

        $linked = ServiceType::query()
            ->whereKey($serviceTypeId)
            ->whereHas(
                'deviceTypes',
                fn ($query) => $query->where('device_types.id', $device->device_type_id),
            )
            ->exists();

        if (! $linked) {
            $validator->errors()->add(
                'service_type_id',
                'The service type is not linked to this device type.',
            );
        }
    }

    private function validateInitialStatus(Validator $validator): void
    {
        $serviceTypeId = (int) $this->input('service_type_id');
        if ($serviceTypeId < 1) {
            return;
        }

        $status = ServiceStatus::findByCode(InitialServiceStatus::CODE);

        if (! $status instanceof ServiceStatus) {
            $validator->errors()->add(
                'service_type_id',
                'Seeded status ['.InitialServiceStatus::CODE.'] is missing.',
            );

            return;
        }

        $mapped = ServiceType::query()
            ->whereKey($serviceTypeId)
            ->whereHas(
                'statuses',
                fn ($query) => $query->where('service_statuses.id', $status->id),
            )
            ->exists();

        if (! $mapped) {
            $validator->errors()->add(
                'service_type_id',
                'Status ['.InitialServiceStatus::CODE.'] is not mapped for this service type.',
            );
        }
    }
}
