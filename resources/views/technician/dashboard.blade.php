@extends('layouts.technician')

@section('content')
    <x-dashboard.hero
        eyebrow="میدان"
        title="داشبورد تکنسین"
        subtitle="ارجاع‌های تخصیص‌یافته به شما و وضعیت پیشنهاد سیستم."
    >
        <x-slot:actions>
            <x-button href="{{ route('technician.assignments.index') }}">ارجاع‌های من</x-button>
        </x-slot:actions>
    </x-dashboard.hero>

    <x-dashboard.section title="خلاصه ارجاع">
        <div class="dash-metric-grid">
            <x-dashboard.metric label="ارجاع‌ها" :value="$assignmentCount" tone="brand" icon="orange" :progress="60" bar="orange" />
            <x-dashboard.metric label="از فهرست پیشنهاد" :value="$suggestedCount" tone="ok" icon="green" :progress="45" bar="green" />
        </div>
    </x-dashboard.section>

    <x-dashboard.section title="لیست ارجاع‌ها" subtitle="برای جزئیات و ثبت گزارش وارد ارجاع شوید">
        @if ($assignments->isEmpty())
            <x-empty-state>ارجاعی ثبت نشده است.</x-empty-state>
        @else
            <div class="dash-table-panel">
                <x-table>
                    <x-slot:head>
                        <th>درخواست</th>
                        <th>تاریخ</th>
                        <th>نوع انتخاب</th>
                        <th></th>
                    </x-slot:head>
                    @foreach ($assignments as $assignment)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $assignment->serviceRequest?->request_number }}</td>
                            <td class="tabular-nums">{{ $assignment->scheduled_date?->toDateString() }}</td>
                            <td>
                                <span class="ui-badge">
                                    {{ $assignment->is_suggested ? 'از پیشنهاد سیستم' : 'انتخاب دستی' }}
                                </span>
                            </td>
                            <td>
                                <a class="ui-link" href="{{ route('technician.assignments.show', $assignment) }}">مشاهده</a>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </div>
        @endif
    </x-dashboard.section>
@endsection
