@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت نوع دستگاه" />

    <x-card>
        <form method="POST" action="{{ route('admin.device-types.store') }}" class="space-y-4">
            @csrf
            @include('admin.device-types._form', ['deviceType' => null])
            <div class="flex gap-2">
                <x-button>ثبت نوع دستگاه</x-button>
                <x-button variant="secondary" href="{{ route('admin.device-types.index') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
