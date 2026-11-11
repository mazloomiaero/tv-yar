@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش نوع دستگاه" subtitle="به‌روزرسانی نام نوع دستگاه." />

    <form method="POST" action="{{ route('admin.device-types.update', $deviceType) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="کاتالوگ" title="ویرایش نوع دستگاه" subtitle="{{ $deviceType->name }}">
            @include('admin.device-types._form', ['deviceType' => $deviceType])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.device-types.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.device-types.destroy', $deviceType) }}" class="ui-card" onsubmit="return confirm('این نوع دستگاه حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف نوع دستگاه</p>
                <p class="mt-1 text-sm text-slate-500">حذف نرم است و سوابق مرتبط حفظ می‌شود.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection
