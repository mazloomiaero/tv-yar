<x-card title="تکنسین‌های پیشنهادی" class="mb-4">
    <p class="mb-3 text-sm text-slate-500">پیشنهاد فقط راهنما است. انتخاب نهایی با شماست.</p>
    @if ($suggestions->isEmpty())
        <p class="text-sm text-slate-500">تکنسین پیشنهادی نیست (نام مهارت باید با نوع خدمت یا مقدار فیلد ذخیره‌شده یکی باشد).</p>
    @else
        <ul class="space-y-1 text-sm">
            @foreach ($suggestions as $suggested)
                <li>
                    {{ $suggested->name }}
                    — open assignments: {{ $suggested->open_assignments_count }}
                </li>
            @endforeach
        </ul>
    @endif
</x-card>

@can('assign', $serviceRequest)
    @if (! $serviceRequest->isClosed())
        <form method="POST" action="{{ route($assignRoute, $serviceRequest) }}" class="mb-4">
            @csrf
            <x-form-shell kicker="ارجاع" title="ارجاع تکنسین" subtitle="زمان برنامه‌ریزی و تکنسین را انتخاب کنید.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="technician_id" class="ui-label">تکنسین</label>
                        <select id="technician_id" name="technician_id" required class="ui-select">
                            <option value="">انتخاب تکنسین</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}" @selected((string) old('technician_id') === (string) $technician->id)>
                                    {{ $technician->name }}
                                    @if ($technician->branch)
                                        ({{ $technician->branch->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="branch_id" class="ui-label">شعبه ارجاع (اختیاری)</label>
                        <select id="branch_id" name="branch_id" class="ui-select">
                            <option value="">هیچ‌کدام</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="scheduled_date" class="ui-label">تاریخ برنامه‌ریزی</label>
                        <input id="scheduled_date" type="date" name="scheduled_date" value="{{ old('scheduled_date', $serviceRequest->preferred_date?->format('Y-m-d')) }}" required class="ui-input">
                    </div>
                    <div>
                        <label for="start_time" class="ui-label">ساعت شروع</label>
                        <input id="start_time" type="time" name="start_time" value="{{ old('start_time', substr((string) $serviceRequest->preferred_start_time, 0, 5)) }}" required class="ui-input">
                    </div>
                    <div>
                        <label for="end_time" class="ui-label">ساعت پایان</label>
                        <input id="end_time" type="time" name="end_time" value="{{ old('end_time', substr((string) $serviceRequest->preferred_end_time, 0, 5)) }}" required class="ui-input">
                    </div>
                </div>

                <x-slot:actions>
                    <x-button>ارجاع تکنسین</x-button>
                </x-slot:actions>
            </x-form-shell>
        </form>
    @endif
@endcan

<x-card title="ارجاع‌ها">
    @if ($serviceRequest->assignments->isEmpty())
        <p class="text-sm text-slate-500">ارجاعی ثبت نشده است.</p>
    @else
        <ul class="space-y-2 text-sm">
            @foreach ($serviceRequest->assignments as $assignment)
                <li>
                    {{ $assignment->technician?->name }}
                    — {{ $assignment->scheduled_date?->format('Y-m-d') }}
                    {{ substr((string) $assignment->start_time, 0, 5) }}–{{ substr((string) $assignment->end_time, 0, 5) }}
                    @if ($assignment->is_suggested)
                        (from suggestion list)
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</x-card>
