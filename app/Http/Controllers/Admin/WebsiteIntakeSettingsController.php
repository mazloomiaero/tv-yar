<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWebsiteIntakeApiKeyRequest;
use App\Http\Requests\Admin\UpdateWebsiteIntakeSettingsRequest;
use App\Models\DeviceType;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\RoleName;
use App\Support\WebsiteIntakeSettingsStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class WebsiteIntakeSettingsController extends Controller
{
    public function edit(WebsiteIntakeSettingsStore $store): View
    {
        $settings = $store->resolved();

        return view('admin.website-intake.edit', [
            'title' => 'Website intake API',
            'settings' => $settings,
            'intakeUrl' => url('/intake/website-request'),
            'deviceTypes' => DeviceType::query()->orderBy('name')->get(),
            'serviceTypes' => ServiceType::query()->orderBy('name')->get(),
            'actors' => User::query()
                ->whereHas('roles', fn ($query) => $query->whereIn('roles.name', [
                    RoleName::CompanyAdmin->value,
                    RoleName::Operator->value,
                ]))
                ->orderBy('username')
                ->get(),
        ]);
    }

    public function update(UpdateWebsiteIntakeSettingsRequest $request, WebsiteIntakeSettingsStore $store): RedirectResponse
    {
        $validated = $request->validated();

        $store->updateDefaults([
            'device_type_id' => (int) $validated['device_type_id'],
            'service_type_id' => (int) $validated['service_type_id'],
            'actor_user_id' => (int) $validated['actor_user_id'],
        ]);

        return redirect()
            ->route('admin.website-intake.edit')
            ->with('status', 'تنظیمات پیش‌فرض ذخیره شد.');
    }

    public function storeKey(StoreWebsiteIntakeApiKeyRequest $request, WebsiteIntakeSettingsStore $store): RedirectResponse
    {
        $entry = $store->createKey((string) $request->validated('name'));

        return redirect()
            ->route('admin.website-intake.edit')
            ->with('status', 'کلید API ساخته شد.')
            ->with('created_api_key_id', $entry['id']);
    }

    public function destroyKey(string $keyId, WebsiteIntakeSettingsStore $store): RedirectResponse
    {
        if (! $store->deleteKey($keyId)) {
            return redirect()
                ->route('admin.website-intake.edit')
                ->withErrors(['keys' => 'کلید پیدا نشد.']);
        }

        return redirect()
            ->route('admin.website-intake.edit')
            ->with('status', 'کلید API حذف شد.');
    }
}
