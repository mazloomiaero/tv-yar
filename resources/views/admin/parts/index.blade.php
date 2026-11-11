@extends('layouts.admin')

@section('content')
    <x-page-header title="کاتالوگ قطعات">
        <x-slot:actions>
            <x-button href="{{ route('admin.parts.create') }}">قطعه جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($parts->isEmpty())
        <x-empty-state>قطعه‌ای ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>نام</th>
                <th>کد</th>
                <th>قیمت واحد</th>
                <th>واحد</th>
                <th>وضعیت</th>
                <th></th>
            </x-slot:head>
            @foreach ($parts as $part)
                <tr>
                    <td>{{ $part->name }}</td>
                    <td>{{ $part->code }}</td>
                    <td>{{ $part->default_unit_price }}</td>
                    <td>{{ $part->unit }}</td>
                    <td>{{ $part->status }}</td>
                    <td class="text-left">
                        <a class="ui-link" href="{{ route('admin.parts.edit', $part) }}">ویرایش</a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
