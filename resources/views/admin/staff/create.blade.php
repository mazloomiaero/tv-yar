@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت کارمند" subtitle="حساب ورود کارکنان داخلی با نقش مناسب." />

    <form method="POST" action="{{ route('admin.staff.store') }}">
        @csrf
        <x-form-shell kicker="کاربران" title="ثبت کارمند" subtitle="نام، نقش، نام کاربری و رمز عبور را وارد کنید.">
            @include('admin.staff._form', ['staff' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.staff.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
