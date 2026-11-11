@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش برند" />

    <x-card class="space-y-6">
        <form method="POST" action="{{ route('admin.brands.update', $brand) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.brands._form', ['brand' => $brand])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.brands.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف</x-button>
        </form>
    </x-card>
@endsection
