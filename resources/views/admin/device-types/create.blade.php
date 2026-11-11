@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت نوع دستگاه" subtitle="نوع دستگاه را برای طبقه‌بندی تعریف کنید." />

    <form method="POST" action="{{ route('admin.device-types.store') }}">
        @csrf
        <x-form-shell kicker="کاتالوگ" title="ثبت نوع دستگاه" subtitle="نوع دستگاه را برای طبقه‌بندی تعریف کنید.">
            @include('admin.device-types._form', ['deviceType' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.device-types.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
