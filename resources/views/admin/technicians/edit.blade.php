@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش تکنسین" />

    <x-card class="space-y-6">
        <form method="POST" action="{{ route('admin.technicians.update', $technician) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.technicians._form', ['technician' => $technician])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.technicians.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.technicians.destroy', $technician) }}">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف</x-button>
        </form>
    </x-card>
@endsection
