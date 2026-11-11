@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت تکنسین" />

    <x-card>
        <form method="POST" action="{{ route('admin.technicians.store') }}" class="space-y-4">
            @csrf
            @include('admin.technicians._form', ['technician' => null])
            <div class="flex gap-2">
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.technicians.index') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
