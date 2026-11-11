<?php

declare(strict_types=1);

namespace App\Actions\Device;

use App\Models\Customer;
use App\Models\Device;
use App\Services\CustomField\CustomFieldService;
use Illuminate\Support\Facades\DB;

final class CreateDevice
{
    public function __construct(private readonly CustomFieldService $customFields) {}

    /**
     * @param  array{
     *     device_type_id: int,
     *     brand_id: int,
     *     model: string,
     *     serial_number: string,
     *     title: string,
     *     purchase_date: string,
     *     warranty_status: string,
     *     warranty_until: string,
     *     notes: string
     * }  $attributes
     * @param  array<int, string>  $customFieldValues
     */
    public function execute(Customer $customer, array $attributes, array $customFieldValues = []): Device
    {
        return DB::transaction(function () use ($customer, $attributes, $customFieldValues): Device {
            $device = Device::query()->create([
                'customer_id' => $customer->id,
                'device_type_id' => $attributes['device_type_id'],
                'brand_id' => $attributes['brand_id'],
                'model' => $attributes['model'],
                'serial_number' => $attributes['serial_number'],
                'title' => $attributes['title'],
                'purchase_date' => $attributes['purchase_date'],
                'warranty_status' => $attributes['warranty_status'],
                'warranty_until' => $attributes['warranty_until'],
                'notes' => $attributes['notes'],
            ]);

            $this->customFields->saveDeviceValues($device, $customFieldValues);

            return $device;
        });
    }
}
