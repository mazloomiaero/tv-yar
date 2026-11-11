@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="ثبت درخواست خدمت" subtitle="{{ $customer->customer_code }} — {{ $customer->first_name }} {{ $customer->last_name }}" />

    @if ($devices->isEmpty())
        <x-empty-state>
            <p>پیش از ثبت درخواست، برای این مشتری دستگاه ثبت کنید.</p>
            @can('create', App\Models\Device::class)
                <p class="mt-3">
                    <a class="ui-link" href="{{ \App\Support\OpsRoute::to('customers.devices.create', $customer) }}">ثبت دستگاه</a>
                </p>
            @endcan
        </x-empty-state>
    @else
        <form method="POST" action="{{ \App\Support\OpsRoute::to('customers.service-requests.store', $customer) }}">
            @csrf
            <x-form-shell kicker="عملیات" title="ثبت درخواست خدمت" subtitle="دستگاه، نوع خدمت و زمان ترجیحی را مشخص کنید.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="device_id" class="ui-label">دستگاه</label>
                        <select id="device_id" name="device_id" required class="ui-select">
                            <option value="">انتخاب دستگاه</option>
                            @foreach ($devices as $device)
                                <option
                                    value="{{ $device->id }}"
                                    data-device-type-id="{{ $device->device_type_id }}"
                                    @selected((string) old('device_id') === (string) $device->id)
                                >
                                    {{ $device->title }} ({{ $device->deviceType?->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="service_type_id" class="ui-label">نوع خدمت</label>
                        <select id="service_type_id" name="service_type_id" required class="ui-select">
                            <option value="">انتخاب نوع خدمت</option>
                            @foreach ($serviceTypesByDeviceType as $deviceTypeId => $serviceTypes)
                                @foreach ($serviceTypes as $serviceType)
                                    <option
                                        value="{{ $serviceType->id }}"
                                        data-device-type-id="{{ $deviceTypeId }}"
                                        class="js-service-type-option"
                                        @selected((string) old('service_type_id') === (string) $serviceType->id)
                                    >
                                        {{ $serviceType->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>

                <fieldset class="ui-fieldset">
                    <legend class="ui-legend">نشانی</legend>
                    @if ($addresses->isNotEmpty())
                        <div>
                            <label for="address_id" class="ui-label">نشانی موجود</label>
                            <select id="address_id" name="address_id" class="ui-select">
                                <option value="">انتخاب نشانی</option>
                                @foreach ($addresses as $address)
                                    <option value="{{ $address->id }}" @selected((string) old('address_id') === (string) $address->id)>
                                        Address #{{ $address->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="create_address" value="1" class="ui-checkbox" @checked(old('create_address'))>
                            ثبت نشانی جدید برای این مشتری
                        </label>
                        <p class="mt-1 text-xs text-slate-500">سطر نشانی فقط پیوند مشتری را نگه می‌دارد؛ ستون شهر و خیابان در شما نیست.</p>
                    </div>
                </fieldset>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="source" class="ui-label">منبع</label>
                        <select id="source" name="source" required class="ui-select">
                            <option value="">انتخاب منبع</option>
                            @foreach ($sources as $source)
                                <option value="{{ $source->value }}" @selected((string) old('source') === $source->value)>
                                    {{ $source->value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="priority" class="ui-label">اولویت</label>
                        <input id="priority" type="text" name="priority" value="{{ old('priority') }}" maxlength="20" required class="ui-input">
                    </div>
                </div>

                <div>
                    <label for="problem_description" class="ui-label">شرح مشکل</label>
                    <textarea id="problem_description" name="problem_description" required class="ui-textarea">{{ old('problem_description') }}</textarea>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="preferred_date" class="ui-label">تاریخ ترجیحی</label>
                        <input id="preferred_date" type="date" name="preferred_date" value="{{ old('preferred_date') }}" required class="ui-input">
                    </div>
                    <div>
                        <label for="preferred_start_time" class="ui-label">ساعت شروع</label>
                        <input id="preferred_start_time" type="time" name="preferred_start_time" value="{{ old('preferred_start_time') }}" required class="ui-input">
                    </div>
                    <div>
                        <label for="preferred_end_time" class="ui-label">ساعت پایان</label>
                        <input id="preferred_end_time" type="time" name="preferred_end_time" value="{{ old('preferred_end_time') }}" required class="ui-input">
                    </div>
                </div>

                <div>
                    <label for="notes" class="ui-label">یادداشت</label>
                    <textarea id="notes" name="notes" class="ui-textarea">{{ old('notes') }}</textarea>
                </div>

                @foreach ($customFieldsByServiceType as $mappedServiceTypeId => $customFields)
                    @if ($customFields->isNotEmpty())
                        <fieldset class="js-service-type-fields ui-fieldset" data-service-type-id="{{ $mappedServiceTypeId }}">
                            <legend class="ui-legend">ویژگی‌های درخواست</legend>
                            @foreach ($customFields as $customField)
                                @php
                                    $currentValue = old('custom_field_values.'.$customField->id, '');
                                @endphp
                                <div>
                                    <label for="sr_custom_field_{{ $customField->id }}" class="ui-label">{{ $customField->name }}</label>
                                    @if ($customField->options->isNotEmpty())
                                        <select id="sr_custom_field_{{ $customField->id }}" name="custom_field_values[{{ $customField->id }}]" class="ui-select">
                                            <option value="">انتخاب</option>
                                            @foreach ($customField->options as $option)
                                                <option value="{{ $option->name }}" @selected((string) $currentValue === (string) $option->name)>{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input
                                            id="sr_custom_field_{{ $customField->id }}"
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

                <x-slot:actions>
                    <x-button>ثبت درخواست خدمت</x-button>
                    <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('customers.edit', $customer) }}">بازگشت</x-button>
                </x-slot:actions>
            </x-form-shell>
        </form>
    @endif

    <script>
        (function () {
            var deviceSelect = document.getElementById('device_id');
            var typeSelect = document.getElementById('service_type_id');
            var typeOptions = document.querySelectorAll('.js-service-type-option');
            var fieldGroups = document.querySelectorAll('.js-service-type-fields');
            if (!deviceSelect || !typeSelect) {
                return;
            }
            function selectedDeviceTypeId() {
                var option = deviceSelect.options[deviceSelect.selectedIndex];
                return option ? option.getAttribute('data-device-type-id') : '';
            }
            function syncTypes() {
                var deviceTypeId = selectedDeviceTypeId();
                typeOptions.forEach(function (option) {
                    var match = option.getAttribute('data-device-type-id') === deviceTypeId;
                    option.hidden = !match;
                    option.disabled = !match;
                });
                var current = typeSelect.options[typeSelect.selectedIndex];
                if (current && current.hidden) {
                    typeSelect.value = '';
                }
                syncFields();
            }
            function syncFields() {
                var selected = typeSelect.value;
                fieldGroups.forEach(function (group) {
                    group.hidden = group.getAttribute('data-service-type-id') !== selected;
                });
            }
            deviceSelect.addEventListener('change', syncTypes);
            typeSelect.addEventListener('change', syncFields);
            syncTypes();
        })();
    </script>
@endsection
