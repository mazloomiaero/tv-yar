<div>
    <label for="device_type_id" class="ui-label">نوع دستگاه</label>
    <select id="device_type_id" name="device_type_id" required class="ui-select">
        <option value="">انتخاب نوع</option>
        @foreach ($deviceTypes as $deviceType)
            <option value="{{ $deviceType->id }}" @selected((string) old('device_type_id', $device?->device_type_id) === (string) $deviceType->id)>{{ $deviceType->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label for="brand_id" class="ui-label">برند</label>
    <select id="brand_id" name="brand_id" required class="ui-select">
        <option value="">انتخاب برند</option>
        @foreach ($brands as $brand)
            <option value="{{ $brand->id }}" @selected((string) old('brand_id', $device?->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label for="title" class="ui-label">عنوان</label>
    <input id="title" type="text" name="title" value="{{ old('title', $device?->title ?? '') }}" maxlength="150" required class="ui-input">
</div>
<div>
    <label for="model" class="ui-label">مدل</label>
    <input id="model" type="text" name="model" value="{{ old('model', $device?->model ?? '') }}" maxlength="150" required class="ui-input">
</div>
<div>
    <label for="serial_number" class="ui-label">شماره سریال</label>
    <input id="serial_number" type="text" name="serial_number" value="{{ old('serial_number', $device?->serial_number ?? '') }}" maxlength="150" required class="ui-input">
</div>
<div>
    <label for="purchase_date" class="ui-label">تاریخ خرید</label>
    <input id="purchase_date" type="date" name="purchase_date" value="{{ old('purchase_date', $device?->purchase_date?->format('Y-m-d') ?? '') }}" required class="ui-input">
</div>
<div>
    <label for="warranty_status" class="ui-label">وضعیت گارانتی</label>
    <input id="warranty_status" type="text" name="warranty_status" value="{{ old('warranty_status', $device?->warranty_status ?? '') }}" maxlength="30" required class="ui-input">
</div>
<div>
    <label for="warranty_until" class="ui-label">پایان گارانتی</label>
    <input id="warranty_until" type="date" name="warranty_until" value="{{ old('warranty_until', $device?->warranty_until?->format('Y-m-d') ?? '') }}" required class="ui-input">
</div>
<div>
    <label for="notes" class="ui-label">یادداشت</label>
    <textarea id="notes" name="notes" class="ui-textarea">{{ old('notes', $device?->notes ?? '') }}</textarea>
</div>

@foreach ($customFieldsByDeviceType as $mappedDeviceTypeId => $customFields)
    @if ($customFields->isNotEmpty())
        <fieldset class="js-device-type-fields ui-fieldset" data-device-type-id="{{ $mappedDeviceTypeId }}">
            <legend class="ui-legend">ویژگی‌های نوع دستگاه</legend>
            @foreach ($customFields as $customField)
                @php
                    $currentValue = old('custom_field_values.'.$customField->id, $deviceCustomFieldValues[$customField->id] ?? '');
                @endphp
                <div>
                    <label for="custom_field_{{ $customField->id }}" class="ui-label">{{ $customField->name }}</label>
                    @if ($customField->options->isNotEmpty())
                        <select id="custom_field_{{ $customField->id }}" name="custom_field_values[{{ $customField->id }}]" class="ui-select">
                            <option value="">انتخاب</option>
                            @foreach ($customField->options as $option)
                                <option value="{{ $option->name }}" @selected((string) $currentValue === (string) $option->name)>{{ $option->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input
                            id="custom_field_{{ $customField->id }}"
                            type="text"
                            name="custom_field_values[{{ $customField->id }}]"
                            value="{{ $currentValue }}"
                            maxlength="255"
                            class="ui-input"
                        >
                    @endif
                </div>
            @endforeach
        </fieldset>
    @endif
@endforeach

<script>
    (function () {
        var typeSelect = document.getElementById('device_type_id');
        var groups = document.querySelectorAll('.js-device-type-fields');
        if (!typeSelect || groups.length === 0) {
            return;
        }
        function sync() {
            var selected = typeSelect.value;
            groups.forEach(function (group) {
                group.hidden = group.getAttribute('data-device-type-id') !== selected;
            });
        }
        typeSelect.addEventListener('change', sync);
        sync();
    })();
</script>
