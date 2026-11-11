@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش قطعه" />

    <x-card class="space-y-6">
        <form method="POST" action="{{ route('admin.parts.update', $part) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.parts._form', ['part' => $part])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.parts.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.parts.destroy', $part) }}">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف</x-button>
        </form>
    </x-card>
@endsection
