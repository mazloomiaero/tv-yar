@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت نوع خدمت" subtitle="نوع خدمت و تنظیمات مرتبط." />

    <form method="POST" action="{{ route('admin.service-types.store') }}">
        @csrf
        <x-form-shell kicker="گردش کار" title="ثبت نوع خدمت" subtitle="نوع خدمت و تنظیمات مرتبط.">
            @include('admin.service-types._form', ['serviceType' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.service-types.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
