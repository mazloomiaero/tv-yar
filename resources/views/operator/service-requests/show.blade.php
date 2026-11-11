@extends(\App\Support\OpsRoute::layout())

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
                <dt>نشانی</dt>
                <dd>#{{ $serviceRequest->address_id }}</dd>
            </div>
            <div>
                <dt>وضعیت</dt>
                <dd>{{ $serviceRequest->status?->name }}</dd>
            </div>
            <div>
                <dt>منبع</dt>
                <dd>{{ $serviceRequest->source }}</dd>
            </div>
            <div>
                <dt>اولویت</dt>
                <dd>{{ $serviceRequest->priority }}</dd>
            </div>
            <div>
                <dt>بازه ترجیحی</dt>
                <dd>{{ $serviceRequest->preferred_date?->format('Y-m-d') }} {{ $serviceRequest->preferred_start_time }}–{{ $serviceRequest->preferred_end_time }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt>شرح مشکل</dt>
                <dd>{{ $serviceRequest->problem_description }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt>یادداشت</dt>
                <dd>{{ $serviceRequest->notes }}</dd>
            </div>
            <div>
                <dt>ثبت‌کننده</dt>
                <dd>{{ $serviceRequest->createdBy?->username }}</dd>
            </div>
            <div>
                <dt>بسته شدن</dt>
                <dd>{{ $serviceRequest->closed_at?->format('Y-m-d H:i') ?? 'open' }}</dd>
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

    <x-card title="تاریخچه وضعیت" class="mb-6">
        <ul class="space-y-2 text-sm">
            @foreach ($serviceRequest->statusHistory as $history)
                <li>
                    {{ $history->status?->name }}
                    @if ($history->changedBy)
                        — {{ $history->changedBy->username }}
                    @endif
                </li>
            @endforeach
        </ul>
    </x-card>

    @include('service-requests._assign')

    <div class="mt-4 space-y-4">
        @can('changeStatus', $serviceRequest)
            <form method="POST" action="{{ \App\Support\OpsRoute::to('service-requests.status', $serviceRequest) }}">
                @csrf
                <x-form-shell kicker="وضعیت" title="تغییر وضعیت" subtitle="وضعیت فعلی درخواست را به‌روز کنید.">
                    <div>
                        <label for="status_id" class="ui-label">وضعیت</label>
                        <select id="status_id" name="status_id" required class="ui-select">
                            @foreach ($allowedStatuses as $status)
                                <option value="{{ $status->id }}" @selected((string) old('status_id', $serviceRequest->status_id) === (string) $status->id)>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <x-slot:actions>
                        <x-button>به‌روزرسانی وضعیت</x-button>
                    </x-slot:actions>
                </x-form-shell>
            </form>
        @endcan

        @can('close', $serviceRequest)
            @if (! $serviceRequest->isClosed())
                <form method="POST" action="{{ \App\Support\OpsRoute::to('service-requests.close', $serviceRequest) }}" class="ui-card" onsubmit="return confirm('این درخواست بسته شود؟')">
                    @csrf
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">بستن درخواست</p>
                            <p class="mt-1 text-sm text-slate-500">پس از بستن، گردش عملیاتی این درخواست متوقف می‌شود.</p>
                        </div>
                        <x-button variant="danger">بستن درخواست</x-button>
                    </div>
                </form>
            @endif
        @endcan
    </div>
@endsection
