@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت وضعیت خدمت" subtitle="وضعیت قابل استفاده در درخواست خدمت." />

    <form method="POST" action="{{ route('admin.service-statuses.store') }}">
        @csrf
        <x-form-shell kicker="گردش کار" title="ثبت وضعیت خدمت" subtitle="وضعیت قابل استفاده در درخواست خدمت.">
            @include('admin.service-statuses._form', ['serviceStatus' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.service-statuses.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
