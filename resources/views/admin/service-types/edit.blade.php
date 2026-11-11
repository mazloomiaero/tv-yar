@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش نوع خدمت" />

    <x-card class="space-y-6">
        <form method="POST" action="{{ route('admin.service-types.update', $serviceType) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.service-types._form', ['serviceType' => $serviceType])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.service-types.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.service-types.destroy', $serviceType) }}">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف</x-button>
        </form>
    </x-card>
@endsection
