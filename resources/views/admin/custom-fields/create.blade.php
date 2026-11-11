@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت فیلد سفارشی" subtitle="ویژگی متغیر برای دستگاه یا درخواست." />

    <form method="POST" action="{{ route('admin.custom-fields.store') }}">
        @csrf
        <x-form-shell kicker="پیکربندی" title="ثبت فیلد سفارشی" subtitle="ویژگی متغیر برای دستگاه یا درخواست.">
            @include('admin.custom-fields._form', ['customField' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.custom-fields.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
