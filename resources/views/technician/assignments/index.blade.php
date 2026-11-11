@extends('layouts.technician')

@section('content')
    <x-page-header title="ارجاع‌های من" />

    <x-card class="mb-6">
        <form method="GET" action="{{ route('technician.assignments.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="scheduled_date" class="ui-label">تاریخ برنامه‌ریزی</label>
                <input id="scheduled_date" type="date" name="scheduled_date" value="{{ $scheduledDate }}" class="ui-input">
            </div>
            <x-button>فیلتر</x-button>
        </form>
    </x-card>

    @if ($assignments->isEmpty())
        <x-empty-state>ارجاعی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>زمان</th>
                <th>درخواست</th>
            </x-slot:head>
            @foreach ($assignments as $assignment)
                <tr>
                    <td>
                        {{ $assignment->scheduled_date?->format('Y-m-d') }}
                        {{ substr((string) $assignment->start_time, 0, 5) }}–{{ substr((string) $assignment->end_time, 0, 5) }}
                    </td>
                    <td>
                        <a class="ui-link" href="{{ route('technician.assignments.show', $assignment) }}">
                            {{ $assignment->serviceRequest?->request_number }}
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
