@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش کارمند" />

    <x-card>
        <form method="POST" action="{{ route('admin.staff.update', $staff) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.staff._form', ['staff' => $staff])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.staff.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.staff.destroy', $staff) }}" class="mt-4" onsubmit="return confirm('این کارمند حذف شود؟')">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف</x-button>
        </form>
    </x-card>
@endsection
