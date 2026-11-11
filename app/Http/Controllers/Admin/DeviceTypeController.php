<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\CatalogDeletionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDeviceTypeRequest;
use App\Http\Requests\Admin\UpdateDeviceTypeRequest;
use App\Models\DeviceType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DeviceTypeController extends Controller
{
    public function index(): View
    {
        return view('admin.device-types.index', [
            'title' => 'Device types',
            'deviceTypes' => DeviceType::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.device-types.create', [
            'title' => 'Create device type',
        ]);
    }

    public function store(StoreDeviceTypeRequest $request): RedirectResponse
    {
        $deviceType = DeviceType::query()->create([
            'name' => $request->validated('name'),
        ]);

        return redirect()
            ->route('admin.device-types.edit', $deviceType)
            ->with('status', 'Device type created.');
    }

    public function edit(DeviceType $deviceType): View
    {
        return view('admin.device-types.edit', [
            'title' => 'Edit device type',
            'deviceType' => $deviceType,
        ]);
    }

    public function update(UpdateDeviceTypeRequest $request, DeviceType $deviceType): RedirectResponse
    {
        $deviceType->fill(['name' => $request->validated('name')]);
        $deviceType->save();

        return redirect()
            ->route('admin.device-types.edit', $deviceType)
            ->with('status', 'Device type updated.');
    }

    public function destroy(DeviceType $deviceType): RedirectResponse
    {
        try {
            $deviceType->delete();
        } catch (CatalogDeletionException $exception) {
            return redirect()
                ->back()
                ->withErrors(['delete' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.device-types.index')
            ->with('status', 'Device type deleted.');
    }
}
