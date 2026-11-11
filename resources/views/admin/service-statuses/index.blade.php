@extends('layouts.admin')

@section('content')
    <x-page-header title="وضعیت‌های خدمت">
        <x-slot:actions>
            <x-button href="{{ route('admin.service-statuses.create') }}">وضعیت جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($serviceStatuses->isEmpty())
        <x-empty-state>وضعیتی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th></th>
            </x-slot:head>
            @foreach ($serviceStatuses as $serviceStatus)
                <tr>
                    <td>{{ $serviceStatus->name }}</td>
                    <td class="text-left">
                        <a class="ui-link" href="{{ route('admin.service-statuses.edit', $serviceStatus) }}">ویرایش</a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
