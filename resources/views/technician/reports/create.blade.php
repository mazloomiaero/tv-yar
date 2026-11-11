@extends('layouts.technician')

@section('content')
    <x-page-header title="ثبت گزارش خدمت" subtitle="درخواست: {{ $serviceRequest?->request_number }}" />

    <x-card>
        <form method="POST" action="{{ route('technician.reports.store', $assignment) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <h2 class="text-base font-semibold text-slate-900">قطعات</h2>
            @for ($i = 0; $i < 3; $i++)
                <fieldset class="ui-fieldset">
                    <legend class="ui-legend">سطر {{ $i + 1 }}</legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="parts_{{ $i }}_part_id" class="ui-label">قطعه کاتالوگ</label>
                            <select id="parts_{{ $i }}_part_id" name="parts[{{ $i }}][part_id]" class="ui-select">
                                <option value="">قطعه متن آزاد</option>
                                @foreach ($parts as $part)
                                    <option value="{{ $part->id }}" @selected((string) old("parts.$i.part_id") === (string) $part->id)>
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
                            <label for="parts_{{ $i }}_unit_price" class="ui-label">قیمت واحد (برای کاتالوگ اختیاری)</label>
                            <input id="parts_{{ $i }}_unit_price" type="text" name="parts[{{ $i }}][unit_price]" value="{{ old("parts.$i.unit_price") }}" class="ui-input">
                        </div>
                    </div>
                </fieldset>
            @endfor

            <h2 class="text-base font-semibold text-slate-900">تصاویر</h2>
            @foreach ($imageKinds as $kind)
                <div>
                    <label for="images_{{ $kind->value }}" class="ui-label">{{ $kind->value }}</label>
                    <input id="images_{{ $kind->value }}" type="file" name="images[{{ $kind->value }}][]" accept="image/jpeg,image/png,image/webp" multiple class="ui-input">
                </div>
            @endforeach

            <div class="flex gap-2">
                <x-button>ذخیره گزارش</x-button>
                <x-button variant="secondary" href="{{ route('technician.assignments.show', $assignment) }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
