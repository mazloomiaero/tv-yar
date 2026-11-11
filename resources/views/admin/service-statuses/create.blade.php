@extends('layouts.admin')

@section('content')
    <x-page-header title="ثبت وضعیت" />

    <x-card>
        <form method="POST" action="{{ route('admin.service-statuses.store') }}" class="space-y-4">
            @csrf
            @include('admin.service-statuses._form', ['serviceStatus' => null])
            <div class="flex gap-2">
                <x-button>ثبت</x-button>
                <x-button variant="secondary" href="{{ route('admin.service-statuses.index') }}">بازگشت</x-button>
            </div>
        </form>
    </x-card>
@endsection
