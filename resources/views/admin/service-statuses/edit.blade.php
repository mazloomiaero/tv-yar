@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش وضعیت" />

    <x-card class="space-y-6">
        <form method="POST" action="{{ route('admin.service-statuses.update', $serviceStatus) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.service-statuses._form', ['serviceStatus' => $serviceStatus])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.service-statuses.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.service-statuses.destroy', $serviceStatus) }}">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف</x-button>
        </form>
    </x-card>
@endsection
