@extends('layouts.technical')

@section('content')
    <x-page-header title="درخواست‌های خدمت" />

    @if ($serviceRequests->isEmpty())
        <x-empty-state>درخواستی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>شماره</th>
                <th>وضعیت</th>
            </x-slot:head>
            @foreach ($serviceRequests as $serviceRequest)
                <tr>
                    <td>
                        <a class="ui-link" href="{{ route('technical.service-requests.show', $serviceRequest) }}">
                            {{ $serviceRequest->request_number }}
                        </a>
                    </td>
                    <td>{{ $serviceRequest->status?->name }}</td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
