@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت برند" />

    <x-card>
        <form method="POST" action="{{ route('admin.brands.store') }}" class="space-y-4">
            @csrf
            @include('admin.brands._form', ['brand' => null])
            <div class="flex gap-2">
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.brands.index') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
