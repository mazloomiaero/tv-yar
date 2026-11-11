<?php

declare(strict_types=1);

namespace App\Actions\ServiceRequest;

use App\Events\ServiceRequestCreated;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Device;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\CustomField\CustomFieldService;
use App\Services\DocumentNumberService;
use App\Support\DocumentType;
use App\Support\InitialServiceStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class CreateServiceRequest
{
    public function __construct(
        private readonly DocumentNumberService $documentNumbers,
        private readonly CustomFieldService $customFields,
    ) {}

    /**
     * @param  array{
     *     device_id: int,
     *     service_type_id: int,
     *     address_id?: int|null,
     *     create_address?: bool,
     *     source: string,
     *     priority: string,
     *     problem_description: string,
     *     preferred_date: string,
     *     preferred_start_time: string,
     *     preferred_end_time: string,
     *     notes: string
     * }  $attributes
     * @param  array<int, string>  $customFieldValues
     */
    public function execute(Customer $customer, User $operator, array $attributes, array $customFieldValues, int $year): ServiceRequest
    {
        $serviceRequest = DB::transaction(function () use ($customer, $operator, $attributes, $customFieldValues, $year): ServiceRequest {
            $device = $this->deviceForCustomer($customer, (int) $attributes['device_id']);
            $serviceType = $this->serviceTypeForDevice($device, (int) $attributes['service_type_id']);
            $status = $this->initialStatusFor($serviceType);
            $address = $this->resolveAddress($customer, $attributes);

            $request = ServiceRequest::query()->create([
                'request_number' => $this->documentNumbers->nextNumber(DocumentType::Request->value, $year),
                'customer_id' => $customer->id,
                'device_id' => $device->id,
                'service_type_id' => $serviceType->id,
                'address_id' => $address->id,
                'status_id' => $status->id,
                'source' => $attributes['source'],
                'priority' => $attributes['priority'],
                'problem_description' => $attributes['problem_description'],
                'preferred_date' => $attributes['preferred_date'],
                'preferred_start_time' => $attributes['preferred_start_time'],
                'preferred_end_time' => $attributes['preferred_end_time'],
                'notes' => $attributes['notes'],
                'created_by' => $operator->id,
                'closed_at' => null,
            ]);

            $request->statusHistory()->create([
                'service_status_id' => $status->id,
                'changed_by' => $operator->id,
            ]);

            $this->customFields->saveServiceRequestValues(
                (int) $request->id,
                (int) $serviceType->id,
                $customFieldValues,
            );

            return $request;
        });

        ServiceRequestCreated::dispatch((int) $serviceRequest->id);

        return $serviceRequest;
    }

    private function deviceForCustomer(Customer $customer, int $deviceId): Device
    {
        $device = Device::query()
            ->where('customer_id', $customer->id)
            ->whereKey($deviceId)
            ->first();

        if (! $device instanceof Device) {
            throw new InvalidArgumentException('Device does not belong to the customer.');
        }

        return $device;
    }

    private function serviceTypeForDevice(Device $device, int $serviceTypeId): ServiceType
    {
        $serviceType = ServiceType::query()->whereKey($serviceTypeId)->first();

        if (! $serviceType instanceof ServiceType) {
            throw new InvalidArgumentException('Service type was not found.');
        }

        $linked = $serviceType->deviceTypes()
            ->where('device_types.id', $device->device_type_id)
            ->exists();

        if (! $linked) {
            throw new InvalidArgumentException('Service type is not linked to the device type.');
        }

        return $serviceType;
    }

    private function initialStatusFor(ServiceType $serviceType): ServiceStatus
    {
        $status = ServiceStatus::findByCode(InitialServiceStatus::CODE);

        if (! $status instanceof ServiceStatus) {
            throw new RuntimeException(
                'Seeded status ['.InitialServiceStatus::CODE.'] is missing.',
            );
        }

        $mapped = $serviceType->statuses()
            ->where('service_statuses.id', $status->id)
            ->exists();

        if (! $mapped) {
            throw new RuntimeException(
                'Status ['.InitialServiceStatus::CODE.'] is not mapped for this service type.',
            );
        }

        return $status;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveAddress(Customer $customer, array $attributes): CustomerAddress
    {
        if (! empty($attributes['create_address'])) {
            return CustomerAddress::query()->create([
                'customer_id' => $customer->id,
            ]);
        }

        $address = CustomerAddress::query()
            ->where('customer_id', $customer->id)
            ->whereKey((int) ($attributes['address_id'] ?? 0))
            ->first();

        if (! $address instanceof CustomerAddress) {
            throw new InvalidArgumentException('Address does not belong to the customer.');
        }

        return $address;
    }
}
