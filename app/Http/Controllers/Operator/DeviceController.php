<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operator;

use App\Actions\Device\CreateDevice;
use App\Actions\Device\UpdateDevice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\StoreDeviceRequest;
use App\Http\Requests\Operator\UpdateDeviceRequest;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Device;
use App\Models\DeviceType;
use App\Services\CustomField\CustomFieldService;
use App\Support\OpsRoute;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class DeviceController extends Controller
{
    use AuthorizesRequests;

    public function create(Customer $customer, CustomFieldService $customFields): View
    {
        $this->authorize('create', Device::class);

        return view('operator.devices.create', [
            'title' => 'Create device',
            'customer' => $customer,
            'deviceTypes' => DeviceType::query()->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'customFieldsByDeviceType' => $customFields->fieldsGroupedByDeviceType(),
            'deviceCustomFieldValues' => [],
        ]);
    }

    public function store(StoreDeviceRequest $request, Customer $customer, CreateDevice $createDevice): RedirectResponse
    {
        $validated = $request->validated();
        $device = $createDevice->execute(
            $customer,
            $this->deviceAttributes($validated),
            $this->customFieldValues($validated),
        );

        return redirect()
            ->route(OpsRoute::name('customers.devices.edit'), [$customer, $device])
            ->with('status', 'Device created.');
    }

    public function edit(Customer $customer, Device $device, CustomFieldService $customFields): View
    {
        $this->authorize('update', $device);

        return view('operator.devices.edit', [
            'title' => 'Edit device',
            'customer' => $customer,
            'device' => $device,
            'deviceTypes' => DeviceType::query()->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'customFieldsByDeviceType' => $customFields->fieldsGroupedByDeviceType(),
            'deviceCustomFieldValues' => $customFields->valuesForDevice($device),
        ]);
    }

    public function update(
        UpdateDeviceRequest $request,
        Customer $customer,
        Device $device,
        UpdateDevice $updateDevice,
    ): RedirectResponse {
        $validated = $request->validated();
        $updateDevice->execute(
            $device,
            $this->deviceAttributes($validated),
            $this->customFieldValues($validated),
        );

        return redirect()
            ->route(OpsRoute::name('customers.devices.edit'), [$customer, $device])
            ->with('status', 'Device updated.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{
     *     device_type_id: int,
     *     brand_id: int,
     *     model: string,
     *     serial_number: string,
     *     title: string,
     *     purchase_date: string,
     *     warranty_status: string,
     *     warranty_until: string,
     *     notes: string
     * }
     */
    private function deviceAttributes(array $validated): array
    {
        return [
            'device_type_id' => (int) $validated['device_type_id'],
            'brand_id' => (int) $validated['brand_id'],
            'model' => (string) $validated['model'],
            'serial_number' => (string) $validated['serial_number'],
            'title' => (string) $validated['title'],
            'purchase_date' => (string) $validated['purchase_date'],
            'warranty_status' => (string) $validated['warranty_status'],
            'warranty_until' => (string) $validated['warranty_until'],
            'notes' => (string) ($validated['notes'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, string>
     */
    private function customFieldValues(array $validated): array
    {
        $raw = $validated['custom_field_values'] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $values = [];
        foreach ($raw as $fieldId => $value) {
            $values[(int) $fieldId] = is_string($value) ? $value : (string) $value;
        }

        return $values;
    }
}
