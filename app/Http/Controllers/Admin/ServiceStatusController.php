<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\CatalogDeletionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceStatusRequest;
use App\Http\Requests\Admin\UpdateServiceStatusRequest;
use App\Models\ServiceStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceStatusController extends Controller
{
    public function index(): View
    {
        return view('admin.service-statuses.index', [
            'title' => 'Service statuses',
            'serviceStatuses' => ServiceStatus::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.service-statuses.create', [
            'title' => 'Create service status',
        ]);
    }

    public function store(StoreServiceStatusRequest $request): RedirectResponse
    {
        $serviceStatus = ServiceStatus::query()->create([
            'name' => $request->validated('name'),
        ]);

        return redirect()
            ->route('admin.service-statuses.edit', $serviceStatus)
            ->with('status', 'Service status created.');
    }

    public function edit(ServiceStatus $serviceStatus): View
    {
        return view('admin.service-statuses.edit', [
            'title' => 'Edit service status',
            'serviceStatus' => $serviceStatus,
        ]);
    }

    public function update(UpdateServiceStatusRequest $request, ServiceStatus $serviceStatus): RedirectResponse
    {
        $serviceStatus->fill(['name' => $request->validated('name')]);
        $serviceStatus->save();

        return redirect()
            ->route('admin.service-statuses.edit', $serviceStatus)
            ->with('status', 'Service status updated.');
    }

    public function destroy(ServiceStatus $serviceStatus): RedirectResponse
    {
        try {
            $serviceStatus->delete();
        } catch (CatalogDeletionException $exception) {
            return redirect()
                ->back()
                ->withErrors(['delete' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.service-statuses.index')
            ->with('status', 'Service status deleted.');
    }
}
