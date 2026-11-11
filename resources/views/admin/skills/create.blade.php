@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت مهارت" />

    <x-card>
        <form method="POST" action="{{ route('admin.skills.store') }}" class="space-y-4">
            @csrf
            @include('admin.skills._form', ['skill' => null])
            <div class="flex gap-2">
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.skills.index') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
