@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش شعبه" />

    <x-card class="space-y-6">
        <form method="POST" action="{{ route('admin.branches.update', $branch) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.branches._form', ['branch' => $branch])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.branches.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف</x-button>
        </form>
    </x-card>
@endsection
