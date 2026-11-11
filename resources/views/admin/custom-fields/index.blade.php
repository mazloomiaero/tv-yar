@extends('layouts.admin')

@section('content')
    <x-page-header title="فیلدهای سفارشی">
        <x-slot:actions>
            <x-button href="{{ route('admin.custom-fields.create') }}">فیلد جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($customFields->isEmpty())
        <x-empty-state>فیلدی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th></th>
            </x-slot:head>
            @foreach ($customFields as $customField)
                <tr>
                    <td>{{ $customField->name }}</td>
                    <td class="text-left">
                        <a class="ui-link" href="{{ route('admin.custom-fields.edit', $customField) }}">ویرایش</a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
