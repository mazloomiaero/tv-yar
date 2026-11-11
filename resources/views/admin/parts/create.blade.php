@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت قطعه" subtitle="قطعه و قیمت پایه را ثبت کنید." />

    <form method="POST" action="{{ route('admin.parts.store') }}">
        @csrf
        <x-form-shell kicker="کاتالوگ" title="ثبت قطعه" subtitle="قطعه و قیمت پایه را ثبت کنید.">
            @include('admin.parts._form', ['part' => null])

            <x-slot:actions>
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.parts.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
