@extends('layouts.customer')

@section('content')
    <x-page-header title="رسید پذیرش {{ $reception->receipt_number }}" />

    <x-card class="mb-6">
        <ul class="space-y-2 text-sm leading-7">
            @foreach ($lines as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ul>
    </x-card>

    <p>
        <a class="ui-link" href="{{ route('customer.service-requests.reception.pdf', $serviceRequest) }}">دانلود PDF</a>
    </p>
@endsection
