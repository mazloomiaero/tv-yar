<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomFieldRequest;
use App\Http\Requests\Admin\UpdateCustomFieldRequest;
use App\Models\CustomField;
use App\Models\DeviceType;
use App\Models\ServiceType;
use App\Services\CustomField\CustomFieldService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CustomFieldController extends Controller
{
    public function index(): View
    {
        return view('admin.custom-fields.index', [
            'title' => 'Custom fields',
            'customFields' => CustomField::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.custom-fields.create', [
            'title' => 'Create custom field',
            'deviceTypes' => DeviceType::query()->orderBy('name')->get(),
            'serviceTypes' => ServiceType::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCustomFieldRequest $request, CustomFieldService $customFields): RedirectResponse
    {
        $payload = $request->catalogPayload();

        $customField = $customFields->saveDefinition(
            null,
            $payload['name'],
            $payload['option_names'],
            $payload['device_type_ids'],
            $payload['service_type_ids'],
        );

        return redirect()
            ->route('admin.custom-fields.edit', $customField)
            ->with('status', 'Custom field created.');
    }

    public function edit(CustomField $customField): View
    {
        $customField->load(['options', 'deviceTypes', 'serviceTypes']);

        return view('admin.custom-fields.edit', [
            'title' => 'Edit custom field',
            'customField' => $customField,
            'deviceTypes' => DeviceType::query()->orderBy('name')->get(),
            'serviceTypes' => ServiceType::query()->orderBy('name')->get(),
        ]);
    }

    public function update(
        UpdateCustomFieldRequest $request,
        CustomField $customField,
        CustomFieldService $customFields,
    ): RedirectResponse {
        $payload = $request->catalogPayload();

        $customFields->saveDefinition(
            $customField,
            $payload['name'],
            $payload['option_names'],
            $payload['device_type_ids'],
            $payload['service_type_ids'],
        );

        return redirect()
            ->route('admin.custom-fields.edit', $customField)
            ->with('status', 'Custom field updated.');
    }

    public function destroy(CustomField $customField): RedirectResponse
    {
        $customField->delete();

        return redirect()
            ->route('admin.custom-fields.index')
            ->with('status', 'Custom field deleted.');
    }
}
