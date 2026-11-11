@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش قطعه" subtitle="به‌روزرسانی کاتالوگ قطعات." />

    <form method="POST" action="{{ route('admin.parts.update', $part) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="کاتالوگ" title="ویرایش قطعه" subtitle="{{ $part->name }}">
            @include('admin.parts._form', ['part' => $part])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.parts.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.parts.destroy', $part) }}" class="ui-card" onsubmit="return confirm('این قطعه حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف قطعه</p>
                <p class="mt-1 text-sm text-slate-500">حذف نرم است؛ قطعات گزارش‌ها حفظ می‌شوند.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection
