@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت فیلد سفارشی" />

    <x-card>
        <form method="POST" action="{{ route('admin.custom-fields.store') }}" class="space-y-4">
            @csrf
            @include('admin.custom-fields._form', ['customField' => null])
            <div class="flex gap-2">
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.custom-fields.index') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
