@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت شعبه" />

    <x-card>
        <form method="POST" action="{{ route('admin.branches.store') }}" class="space-y-4">
            @csrf
            @include('admin.branches._form', ['branch' => null])
            <div class="flex gap-2">
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.branches.index') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
