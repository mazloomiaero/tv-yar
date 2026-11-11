@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش نوع خدمت" subtitle="به‌روزرسانی نوع خدمت و نگاشت‌ها." />

    <form method="POST" action="{{ route('admin.service-types.update', $serviceType) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="گردش کار" title="ویرایش نوع خدمت" subtitle="{{ $serviceType->name }}">
            @include('admin.service-types._form', ['serviceType' => $serviceType])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.service-types.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.service-types.destroy', $serviceType) }}" class="ui-card" onsubmit="return confirm('این نوع خدمت حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف نوع خدمت</p>
                <p class="mt-1 text-sm text-slate-500">حذف نرم است؛ درخواست‌های قبلی حفظ می‌شوند.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection
