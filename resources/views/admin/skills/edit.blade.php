@extends('layouts.admin')

@section('content')
    <x-page-header title="ویرایش مهارت" />

    <x-card class="space-y-6">
        <form method="POST" action="{{ route('admin.skills.update', $skill) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.skills._form', ['skill' => $skill])
            <div class="flex flex-wrap gap-2">
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.skills.index') }}">بازگشت</x-button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}">
            @csrf
            @method('DELETE')
            <x-button variant="danger">حذف</x-button>
        </form>
    </x-card>
@endsection
