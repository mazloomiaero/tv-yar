@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش وضعیت خدمت" subtitle="به‌روزرسانی وضعیت قابل استفاده در درخواست." />

    <form method="POST" action="{{ route('admin.service-statuses.update', $serviceStatus) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="گردش کار" title="ویرایش وضعیت" subtitle="{{ $serviceStatus->name }}">
            @include('admin.service-statuses._form', ['serviceStatus' => $serviceStatus])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.service-statuses.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.service-statuses.destroy', $serviceStatus) }}" class="ui-card" onsubmit="return confirm('این وضعیت حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف وضعیت</p>
                <p class="mt-1 text-sm text-slate-500">حذف نرم است؛ تاریخچه وضعیت‌ها حفظ می‌شود.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection
