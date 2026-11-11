@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش مهارت" subtitle="به‌روزرسانی نام مهارت تکنسین." />

    <form method="POST" action="{{ route('admin.skills.update', $skill) }}" class="mb-4">
        @csrf
        @method('PUT')
        <x-form-shell kicker="تکنسین" title="ویرایش مهارت" subtitle="{{ $skill->name }}">
            @include('admin.skills._form', ['skill' => $skill])

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.skills.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" class="ui-card" onsubmit="return confirm('این مهارت حذف شود؟')">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">حذف مهارت</p>
                <p class="mt-1 text-sm text-slate-500">حذف نرم است و سوابق مرتبط حفظ می‌شود.</p>
            </div>
            <x-button variant="danger">حذف</x-button>
        </div>
    </form>
@endsection
