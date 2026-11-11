@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت برند" subtitle="برند جدید را برای دستگاه‌ها تعریف کنید." />

    <form method="POST" action="{{ route('admin.brands.store') }}">
        @csrf
        <x-form-shell kicker="کاتالوگ" title="ثبت برند" subtitle="برند جدید را برای دستگاه‌ها تعریف کنید.">
            @include('admin.brands._form', ['brand' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.brands.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
