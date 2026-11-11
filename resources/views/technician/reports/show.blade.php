@extends('layouts.technician')

@section('content')
    <x-page-header title="گزارش خدمت" subtitle="درخواست: {{ $report->serviceRequest?->request_number }}" />

    <x-card title="قطعات" class="mb-6">
        @if ($report->parts->isEmpty())
            <p class="text-sm text-slate-500">قطعه‌ای ثبت نشده است.</p>
        @else
            <x-table>
                <x-slot:head>
                    <th>نام</th>
                    <th>مقدار</th>
                    <th>قیمت واحد</th>
                    <th>جمع</th>
                </x-slot:head>
                @foreach ($report->parts as $line)
                    <tr>
                        <td>{{ $line->name }}</td>
                        <td>{{ $line->quantity }}</td>
                        <td>{{ $line->unit_price }}</td>
                        <td>{{ $line->total_price }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>

    <x-card title="تصاویر">
        @if ($report->images->isEmpty())
            <p class="text-sm text-slate-500">تصویری ثبت نشده است.</p>
        @else
            <ul class="space-y-1 text-sm">
                @foreach ($report->images as $image)
                    <li>{{ $image->kind }}: {{ $image->path }}</li>
                @endforeach
            </ul>
        @endif
    </x-card>
@endsection
