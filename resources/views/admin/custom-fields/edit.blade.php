@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش فیلد سفارشی" subtitle="به‌روزرسانی ویژگی متغیر دستگاه یا درخواست." />

    <form method="POST" action="{{ route('admin.custom-fields.update', $customField) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="پیکربندی" title="ویرایش فیلد سفارشی" subtitle="{{ $customField->name }}">
            @include('admin.custom-fields._form', ['customField' => $customField])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.custom-fields.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.custom-fields.destroy', $customField) }}" class="ui-card" onsubmit="return confirm('این فیلد سفارشی حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف فیلد سفارشی</p>
                <p class="mt-1 text-sm text-slate-500">حذف نرم است؛ مقادیر ثبت‌شده قبلی حفظ می‌شوند.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection
