@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش برند" subtitle="به‌روزرسانی نام برند در کاتالوگ." />

    <form method="POST" action="{{ route('admin.brands.update', $brand) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="کاتالوگ" title="ویرایش برند" subtitle="{{ $brand->name }}">
            @include('admin.brands._form', ['brand' => $brand])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.brands.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="ui-card" onsubmit="return confirm('این برند حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف برند</p>
                <p class="mt-1 text-sm text-slate-500">حذف نرم است و سوابق مرتبط حفظ می‌شود.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection
