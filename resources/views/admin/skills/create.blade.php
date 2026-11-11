@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت مهارت" subtitle="مهارت جدید برای پیشنهاد ارجاع." />

    <form method="POST" action="{{ route('admin.skills.store') }}">
        @csrf
        <x-form-shell kicker="تکنسین" title="ثبت مهارت" subtitle="مهارت جدید برای پیشنهاد ارجاع.">
            @include('admin.skills._form', ['skill' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.skills.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
