@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت کارمند" />

    <x-card>
        <form method="POST" action="{{ route('admin.staff.store') }}" class="space-y-4">
            @csrf
            @include('admin.staff._form', ['staff' => null])
            <div class="flex gap-2">
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.staff.index') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
