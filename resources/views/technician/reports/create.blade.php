@extends('layouts.technician')

@section('content')
    <x-page-header title="ثبت گزارش خدمت" subtitle="درخواست: {{ $serviceRequest?->request_number }}" />

    <form method="POST" action="{{ route('technician.reports.store', $assignment) }}" enctype="multipart/form-data">
        @csrf
        <x-form-shell kicker="گزارش" title="ثبت گزارش خدمت" subtitle="شرح کار، قطعات مصرفی و تصاویر قبل/بعد را ثبت کنید.">
            <div>
                <label for="description" class="ui-label">شرح گزارش</label>
                <textarea id="description" name="description" required minlength="10" class="ui-textarea">{{ old('description') }}</textarea>
                <p class="ui-form-section__hint">حداقل ۱۰ نویسه.</p>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="ui-form-section">
                <h3 class="ui-form-section__title">قطعات</h3>
                <p class="ui-form-section__hint">قیمت قطعه کاتالوگ ثابت است. برای قطعه آزاد، نام و قیمت واحد الزامی است.</p>
            </div>

            @for ($i = 0; $i < 3; $i++)
                @php
                    $selectedPartId = (string) old("parts.$i.part_id");
                    $selectedPart = $parts->first(fn ($part): bool => (string) $part->id === $selectedPartId);
                    $priceLocked = $selectedPart !== null;
                    $priceValue = $priceLocked ? $selectedPart->default_unit_price : old("parts.$i.unit_price");
                @endphp
                <fieldset class="ui-fieldset" data-part-row>
                    <legend class="ui-legend">سطر {{ $i + 1 }}</legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="parts_{{ $i }}_part_id" class="ui-label">قطعه کاتالوگ</label>
                            <select id="parts_{{ $i }}_part_id" name="parts[{{ $i }}][part_id]" class="ui-select" data-part-select>
                                <option value="">قطعه متن آزاد</option>
                                @foreach ($parts as $part)
                                    <option value="{{ $part->id }}" data-price="{{ $part->default_unit_price }}" @selected($selectedPartId === (string) $part->id)>
                                        {{ $part->name }} ({{ $part->default_unit_price }} / {{ $part->unit }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="parts_{{ $i }}_name" class="ui-label">نام (برای قطعه آزاد الزامی است)</label>
                            <input id="parts_{{ $i }}_name" type="text" name="parts[{{ $i }}][name]" value="{{ old("parts.$i.name") }}" maxlength="150" class="ui-input">
                        </div>
                        <div>
                            <label for="parts_{{ $i }}_quantity" class="ui-label">مقدار</label>
                            <input id="parts_{{ $i }}_quantity" type="text" name="parts[{{ $i }}][quantity]" value="{{ old("parts.$i.quantity") }}" class="ui-input">
                        </div>
                        <div>
                            <label for="parts_{{ $i }}_unit_price" class="ui-label">قیمت واحد</label>
                            <input id="parts_{{ $i }}_unit_price" type="text" name="parts[{{ $i }}][unit_price]" value="{{ $priceValue }}" class="ui-input" data-unit-price @readonly($priceLocked)>
                            <p class="ui-form-section__hint">برای کاتالوگ فقط نمایش داده می‌شود. برای قطعه آزاد قابل ویرایش است.</p>
                        </div>
                    </div>
                </fieldset>
            @endfor

            <div class="ui-form-section">
                <h3 class="ui-form-section__title">تصاویر</h3>
                <p class="ui-form-section__hint">فرمت‌های مجاز: JPEG، PNG، WebP</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($imageKinds as $kind)
                    <div>
                        <label for="images_{{ $kind->value }}" class="ui-label">{{ $kind->value }}</label>
                        <input id="images_{{ $kind->value }}" type="file" name="images[{{ $kind->value }}][]" accept="image/jpeg,image/png,image/webp" multiple class="ui-file">
                    </div>
                @endforeach
            </div>

            <x-slot:actions>
                <x-button>ذخیره گزارش</x-button>
                <x-button variant="secondary" href="{{ route('technician.assignments.show', $assignment) }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <script>
        (function () {
            document.querySelectorAll('[data-part-row]').forEach(function (row) {
                var select = row.querySelector('[data-part-select]');
                var price = row.querySelector('[data-unit-price]');
                if (!select || !price) {
                    return;
                }

                function sync() {
                    var option = select.options[select.selectedIndex];
                    var catalogPrice = option ? option.getAttribute('data-price') : '';

                    if (catalogPrice) {
                        if (!price.readOnly) {
                            price.setAttribute('data-free-price', price.value);
                        }
                        price.value = catalogPrice;
                        price.readOnly = true;
                        return;
                    }

                    if (price.readOnly) {
                        var saved = price.getAttribute('data-free-price');
                        price.value = saved === null ? '' : saved;
                        price.readOnly = false;
                    }
                }

                select.addEventListener('change', sync);
                sync();
            });
        })();
    </script>
@endsection
