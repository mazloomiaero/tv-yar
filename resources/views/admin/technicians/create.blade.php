@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت تکنسین" subtitle="شماره همراه همان نام کاربری ورود به پنل تکنسین است." />

    <form method="POST" action="{{ route('admin.technicians.store') }}">
        @csrf
        <x-form-shell kicker="تکنسین" title="ثبت تکنسین" subtitle="مشخصات، شعبه، رمز عبور و مهارت‌ها را کامل کنید.">
            @include('admin.technicians._form', ['technician' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.technicians.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
