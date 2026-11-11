<div>
    <label for="name" class="ui-label">نام</label>
    <input id="name" type="text" name="name" value="{{ old('name', $serviceType?->name ?? '') }}" maxlength="150" required class="ui-input">
</div>

<fieldset class="ui-fieldset">
    <legend class="ui-legend">انواع دستگاه</legend>
    <div class="ui-check-grid">
        @forelse ($deviceTypes as $deviceType)
            <label class="ui-check-pill">
                <input
                    type="checkbox"
                    name="device_type_ids[]"
                    value="{{ $deviceType->id }}"
                    class="ui-checkbox"
                    @checked(in_array($deviceType->id, old('device_type_ids', $serviceType?->deviceTypes?->modelKeys() ?? []), false))
                >
                {{ $deviceType->name }}
            </label>
        @empty
            <p class="text-sm text-slate-500">نوع دستگاهی ثبت نشده است.</p>
        @endforelse
    </div>
</fieldset>

<fieldset class="ui-fieldset">
    <legend class="ui-legend">وضعیت‌های مجاز</legend>
    <div class="ui-check-grid">
        @forelse ($serviceStatuses as $serviceStatus)
            <label class="ui-check-pill">
                <input
                    type="checkbox"
                    name="service_status_ids[]"
                    value="{{ $serviceStatus->id }}"
                    class="ui-checkbox"
                    @checked(in_array($serviceStatus->id, old('service_status_ids', $serviceType?->statuses?->modelKeys() ?? []), false))
                >
                {{ $serviceStatus->name }}
            </label>
        @empty
            <p class="text-sm text-slate-500">وضعیتی ثبت نشده است.</p>
        @endforelse
    </div>
</fieldset>
