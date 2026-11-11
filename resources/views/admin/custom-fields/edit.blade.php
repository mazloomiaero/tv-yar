@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش فیلد سفارشی" />

    <x-card class="space-y-6">
        <form method="POST" action="{{ route('admin.custom-fields.update', $customField) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.custom-fields._form', ['customField' => $customField])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.custom-fields.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.custom-fields.destroy', $customField) }}">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف</x-button>
        </form>
    </x-card>
@endsection
