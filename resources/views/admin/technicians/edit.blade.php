@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش تکنسین" subtitle="به‌روزرسانی پروفایل و دسترسی ورود." />

    <form method="POST" action="{{ route('admin.technicians.update', $technician) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="تکنسین" title="ویرایش تکنسین" subtitle="{{ $technician->user?->username ?? $technician->name }}">
            @include('admin.technicians._form', ['technician' => $technician])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.technicians.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.technicians.destroy', $technician) }}" class="ui-card" onsubmit="return confirm('این تکنسین حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف تکنسین</p>
                <p class="mt-1 text-sm text-slate-500">حذف نرم است؛ تاریخچه ارجاع‌ها حفظ می‌شود.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection