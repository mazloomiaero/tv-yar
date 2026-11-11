@extends('layouts.technical')

@section('content')
    <x-page-header title="{{ $serviceRequest->request_number }}" />

    <x-card class="mb-6">
        <dl class="ui-dl">
            <div>
                <dt>مشتری</dt>
                <dd>{{ $serviceRequest->customer?->customer_code }} — {{ $serviceRequest->customer?->first_name }} {{ $serviceRequest->customer?->last_name }}</dd>
            </div>
            <div>
                <dt>دستگاه</dt>
                <dd>{{ $serviceRequest->device?->title }} ({{ $serviceRequest->device?->deviceType?->name }})</dd>
            </div>
            <div>
                <dt>نوع خدمت</dt>
                <dd>{{ $serviceRequest->serviceType?->name }}</dd>
            </div>
            <div>
                <dt>وضعیت</dt>
                <dd>{{ $serviceRequest->status?->name }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt>شرح مشکل</dt>
                <dd>{{ $serviceRequest->problem_description }}</dd>
            </div>
        </dl>
    </x-card>

    @if ($customFields->isNotEmpty())
        <x-card title="ویژگی‌های درخواست" class="mb-6">
            <ul class="space-y-2 text-sm">
                @foreach ($customFields as $customField)
                    <li>{{ $customField->name }}: {{ $customFieldValues[$customField->id] ?? '' }}</li>
                @endforeach
            </ul>
        </x-card>
    @endif

    @include('service-requests._assign')

    <div class="mt-4">
        @if ($serviceRequest->reception)
            <x-card title="پذیرش کارگاهی" class="mb-4">
                <dl class="ui-dl mb-4">
                    <div>
                        <dt>رسید</dt>
                        <dd>{{ $serviceRequest->reception->receipt_number }}</dd>
                    </div>
                    <div>
                        <dt>تاریخ پذیرش</dt>
                        <dd>{{ $serviceRequest->reception->received_date?->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt>پذیرش‌کننده</dt>
                        <dd>{{ $serviceRequest->reception->receivedBy?->username }}</dd>
                    </div>
                    <div>
                        <dt>ظاهر دستگاه</dt>
                        <dd>{{ $serviceRequest->reception->appearance }}</dd>
                    </div>
                    <div>
                        <dt>لوازم همراه</dt>
                        <dd>{{ $serviceRequest->reception->accompanying_items }}</dd>
                    </div>
                    <div>
                        <dt>علت</dt>
                        <dd>{{ $serviceRequest->reception->reason }}</dd>
                    </div>
                    <div>
                        <dt>یادداشت</dt>
                        <dd>{{ $serviceRequest->reception->notes }}</dd>
                    </div>
                    <div>
                        <dt>تاریخ تحویل</dt>
                        <dd>{{ $serviceRequest->reception->delivery_date?->format('Y-m-d') ?? '' }}</dd>
                    </div>
                </dl>
                <p>
                    <a class="ui-link" href="{{ route('technical.service-requests.receptions.pdf', $serviceRequest) }}">دانلود PDF رسید</a>
                </p>
            </x-card>

            @can('changeStatus', $serviceRequest)
                @if (! $serviceRequest->reception->isDelivered())
                    <form method="POST" action="{{ route('technical.service-requests.receptions.deliver', $serviceRequest) }}">
                        @csrf
                        <x-form-shell kicker="کارگاه" title="تحویل دستگاه" subtitle="تاریخ تحویل به مشتری را ثبت کنید.">
                            <div>
                                <label for="delivery_date" class="ui-label">تاریخ تحویل</label>
                                <input id="delivery_date" type="date" name="delivery_date" value="{{ old('delivery_date') }}" required class="ui-input">
                            </div>
                            <x-slot:actions>
                                <x-button>تحویل دستگاه</x-button>
                            </x-slot:actions>
                        </x-form-shell>
                    </form>
                @endif
            @endcan
        @else
            @can('changeStatus', $serviceRequest)
                <form method="POST" action="{{ route('technical.service-requests.receptions.store', $serviceRequest) }}">
                    @csrf
                    <x-form-shell kicker="کارگاه" title="پذیرش دستگاه" subtitle="رسید پذیرش کارگاهی را برای همین درخواست ثبت کنید.">
                        <div>
                            <label for="received_date" class="ui-label">تاریخ پذیرش</label>
                            <input id="received_date" type="date" name="received_date" value="{{ old('received_date') }}" required class="ui-input">
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="appearance" class="ui-label">ظاهر دستگاه</label>
                                <textarea id="appearance" name="appearance" class="ui-textarea">{{ old('appearance') }}</textarea>
                            </div>
                            <div>
                                <label for="accompanying_items" class="ui-label">لوازم همراه</label>
                                <textarea id="accompanying_items" name="accompanying_items" class="ui-textarea">{{ old('accompanying_items') }}</textarea>
                            </div>
                        </div>
                        <div>
                            <label for="reason" class="ui-label">علت</label>
                            <textarea id="reason" name="reason" class="ui-textarea">{{ old('reason') }}</textarea>
                        </div>
                        <div>
                            <label for="notes" class="ui-label">یادداشت</label>
                            <textarea id="notes" name="notes" class="ui-textarea">{{ old('notes') }}</textarea>
                        </div>
                        <x-slot:actions>
                            <x-button>پذیرش دستگاه</x-button>
                        </x-slot:actions>
                    </x-form-shell>
                </form>
            @endcan
        @endif
    </div>
@endsection
