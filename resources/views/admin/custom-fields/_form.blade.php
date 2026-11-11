<div>
    <label for="name" class="ui-label">نام</label>
    <input id="name" type="text" name="name" value="{{ old('name', $customField?->name ?? '') }}" maxlength="150" required class="ui-input">
</div>

<div>
    <label for="option_names" class="ui-label">گزینه‌های انتخابی (هر خط یک گزینه؛ برای متن آزاد خالی بگذارید)</label>
    <textarea id="option_names" name="option_names" rows="5" class="ui-textarea">{{ old('option_names', isset($customField) ? $customField->options->pluck('name')->implode("\n") : '') }}</textarea>
</div>

<fieldset class="ui-fieldset">
    <legend class="ui-legend">انواع دستگاه</legend>
    @forelse ($deviceTypes as $deviceType)
        <div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    name="device_type_ids[]"
                    value="{{ $deviceType->id }}"
                    class="ui-checkbox"
                    @checked(in_array($deviceType->id, old('device_type_ids', $customField?->deviceTypes?->modelKeys() ?? []), false))
                >
                {{ $deviceType->name }}
            </label>
        </div>
    @empty
        <p class="text-sm text-slate-500">نوع دستگاهی ثبت نشده است.</p>
    @endforelse
</fieldset>

<fieldset class="ui-fieldset">
    <legend class="ui-legend">انواع خدمات</legend>
    @forelse ($serviceTypes as $serviceType)
        <div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    name="service_type_ids[]"
                    value="{{ $serviceType->id }}"
                    class="ui-checkbox"
                    @checked(in_array($serviceType->id, old('service_type_ids', $customField?->serviceTypes?->modelKeys() ?? []), false))
                >
                {{ $serviceType->name }}
            </label>
        </div>
    @empty
        <p class="text-sm text-slate-500">نوع خدمتی ثبت نشده است.</p>
    @endforelse
</fieldset>
