@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش نوع دستگاه" />

    <x-card class="space-y-6">
        <form method="POST" action="{{ route('admin.device-types.update', $deviceType) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.device-types._form', ['deviceType' => $deviceType])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره نوع دستگاه</x-button>
                <x-button variant="secondary" href="{{ route('admin.device-types.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.device-types.destroy', $deviceType) }}">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف نوع دستگاه</x-button>
        </form>
    </x-card>
@endsection
