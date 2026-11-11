@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت قطعه" />

    <x-card>
        <form method="POST" action="{{ route('admin.parts.store') }}" class="space-y-4">
            @csrf
            @include('admin.parts._form', ['part' => null])
            <div class="flex gap-2">
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.parts.index') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
