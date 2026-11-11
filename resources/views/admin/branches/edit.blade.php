@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش شعبه" subtitle="به‌روزرسانی مشخصات شعبه داخلی." />

    <form method="POST" action="{{ route('admin.branches.update', $branch) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="سازمان" title="ویرایش شعبه" subtitle="{{ $branch->name }}">
            @include('admin.branches._form', ['branch' => $branch])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.branches.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="ui-card" onsubmit="return confirm('این شعبه حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف شعبه</p>
                <p class="mt-1 text-sm text-slate-500">حذف نرم است و ارجاع‌های قبلی حفظ می‌شود.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection
