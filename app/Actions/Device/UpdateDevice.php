<?php

declare(strict_types=1);

namespace App\Actions\Device;

use App\Models\Device;
use App\Services\CustomField\CustomFieldService;
use Illuminate\Support\Facades\DB;

final class UpdateDevice
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
    public function execute(Device $device, array $attributes, array $customFieldValues = []): Device
    {
        return DB::transaction(function () use ($device, $attributes, $customFieldValues): Device {
            $device->fill([
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

            $device->save();

            $this->customFields->saveDeviceValues($device, $customFieldValues);

            return $device;
        });
    }
}
