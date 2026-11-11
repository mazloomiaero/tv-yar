@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت شعبه" subtitle="شعبه فیزیکی این نصب." />

    <form method="POST" action="{{ route('admin.branches.store') }}">
        @csrf
        <x-form-shell kicker="سازمان" title="ثبت شعبه" subtitle="شعبه فیزیکی این نصب.">
            @include('admin.branches._form', ['branch' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.branches.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
