<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceTypeRequest;
use App\Http\Requests\Admin\UpdateServiceTypeRequest;
use App\Models\DeviceType;
use App\Models\ServiceStatus;
use App\Models\ServiceType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceTypeController extends Controller
{
    public function index(): View
    {
        return view('admin.service-types.index', [
            'title' => 'Service types',
            'serviceTypes' => ServiceType::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.service-types.create', [
            'title' => 'Create service type',
            'deviceTypes' => DeviceType::query()->orderBy('name')->get(),
            'serviceStatuses' => ServiceStatus::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreServiceTypeRequest $request): RedirectResponse
    {
        $payload = $request->catalogPayload();

        $serviceType = ServiceType::query()->create([
            'name' => $payload['name'],
        ]);

        $serviceType->deviceTypes()->sync($payload['device_type_ids']);
        $serviceType->statuses()->sync($payload['service_status_ids']);

        return redirect()
            ->route('admin.service-types.edit', $serviceType)
            ->with('status', 'Service type created.');
    }

    public function edit(ServiceType $serviceType): View
    {
        $serviceType->load(['deviceTypes', 'statuses']);

        return view('admin.service-types.edit', [
            'title' => 'Edit service type',
            'serviceType' => $serviceType,
            'deviceTypes' => DeviceType::query()->orderBy('name')->get(),
            'serviceStatuses' => ServiceStatus::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $payload = $request->catalogPayload();

        $serviceType->fill(['name' => $payload['name']]);
        $serviceType->save();

        $serviceType->deviceTypes()->sync($payload['device_type_ids']);
        $serviceType->statuses()->sync($payload['service_status_ids']);

        return redirect()
            ->route('admin.service-types.edit', $serviceType)
            ->with('status', 'Service type updated.');
    }

    public function destroy(ServiceType $serviceType): RedirectResponse
    {
        $serviceType->delete();

        return redirect()
            ->route('admin.service-types.index')
            ->with('status', 'Service type deleted.');
    }
}
