@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش کارمند" subtitle="به‌روزرسانی مشخصات و دسترسی ورود." />

    <form method="POST" action="{{ route('admin.staff.update', $staff) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="کاربران" title="ویرایش کارمند" subtitle="{{ $staff->username }}">
            @include('admin.staff._form', ['staff' => $staff])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.staff.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.staff.destroy', $staff) }}" class="ui-card" onsubmit="return confirm('این کارمند حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف کارمند</p>
                <p class="mt-1 text-sm text-slate-500">این عمل حذف نرم است و تاریخچه را پاک نمی‌کند.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection
