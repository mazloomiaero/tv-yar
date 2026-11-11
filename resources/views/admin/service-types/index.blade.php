@extends('layouts.admin')

@section('content')
    <x-page-header title="انواع خدمات">
        <x-slot:actions>
            <x-button href="{{ route('admin.service-types.create') }}">نوع خدمت جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($serviceTypes->isEmpty())
        <x-empty-state>نوع خدمتی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th></th>
            </x-slot:head>
            @foreach ($serviceTypes as $serviceType)
                <tr>
                    <td>{{ $serviceType->name }}</td>
                    <td class="text-left">
                        <a class="ui-link" href="{{ route('admin.service-types.edit', $serviceType) }}">ویرایش</a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
