@extends('layouts.admin')

@section('content')
    <x-page-header title="API فرم وب‌سایت" subtitle="کلید یک‌طرفه برای پلاگین وردپرس؛ با این API فقط ثبت مشتری و درخواست انجام می‌شود." />

    <form method="POST" action="{{ route('admin.website-intake.update') }}" class="mb-5">
        @csrf
        @method('PUT')
        <x-form-shell kicker="پیش‌فرض‌ها" title="تنظیمات اتصال و ثبت درخواست" subtitle="آدرس endpoint، نوع دستگاه، نوع خدمت و کاربر ثبت‌کننده را اینجا تنظیم کنید.">
            <div>
                <label class="ui-label">آدرس endpoint</label>
                <div class="flex flex-wrap items-center gap-2">
                    <input id="intake-endpoint" type="text" class="ui-input flex-1" value="{{ $intakeUrl }}" readonly dir="ltr">
                    <x-button type="button" variant="secondary" data-copy-target="#intake-endpoint" title="کپی آدرس">
                        <span data-copy-label>کپی</span>
                    </x-button>
                </div>
            </div>

            <div>
                <label for="device_type_id" class="ui-label">نوع دستگاه پیش‌فرض</label>
                <select id="device_type_id" name="device_type_id" required class="ui-input">
                    <option value="">انتخاب کنید</option>
                    @foreach ($deviceTypes as $deviceType)
                        <option value="{{ $deviceType->id }}" @selected((string) old('device_type_id', $settings['device_type_id']) === (string) $deviceType->id)>
                            {{ $deviceType->name }}
                        </option>
                    @endforeach
                </select>
                @error('device_type_id')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="service_type_id" class="ui-label">نوع خدمت پیش‌فرض</label>
                <select id="service_type_id" name="service_type_id" required class="ui-input">
                    <option value="">انتخاب کنید</option>
                    @foreach ($serviceTypes as $serviceType)
                        <option value="{{ $serviceType->id }}" @selected((string) old('service_type_id', $settings['service_type_id']) === (string) $serviceType->id)>
                            {{ $serviceType->name }}
                        </option>
                    @endforeach
                </select>
                @error('service_type_id')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="actor_user_id" class="ui-label">کاربر ثبت‌کننده (created_by)</label>
                <select id="actor_user_id" name="actor_user_id" required class="ui-input">
                    <option value="">انتخاب کنید</option>
                    @foreach ($actors as $actor)
                        <option value="{{ $actor->id }}" @selected((string) old('actor_user_id', $settings['actor_user_id']) === (string) $actor->id)>
                            {{ $actor->username }}
                        </option>
                    @endforeach
                </select>
                @error('actor_user_id')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <x-slot:actions>
                <x-button>ذخیره پیش‌فرض‌ها</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <div class="ui-card">
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">API Keys</p>
            <h2 class="mt-1 text-lg font-semibold text-slate-900">کلیدهای API</h2>
            <p class="mt-1 text-sm text-slate-500">کلید بسازید و در پلاگین وردپرس قرار دهید.</p>
        </div>

        <form method="POST" action="{{ route('admin.website-intake.keys.store') }}">
            @csrf
            <label for="key_name" class="ui-label">نام کلید جدید</label>
            <div class="flex flex-wrap items-center gap-2">
                <input
                    id="key_name"
                    type="text"
                    name="name"
                    value="{{ old('name', 'وردپرس سایت') }}"
                    maxlength="100"
                    required
                    class="ui-input min-w-[16rem] flex-1"
                >
                <x-button class="shrink-0">ساخت کلید جدید</x-button>
            </div>
            @error('name')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </form>
        @error('keys')
            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <div class="mt-6 border-t border-slate-100 pt-5">
            <h3 class="mb-3 text-base font-semibold text-slate-900">کلیدهای ساخته‌شده</h3>

            @if ($settings['keys'] === [])
                <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                    هنوز کلیدی ساخته نشده است.
                </p>
            @else
                <ul class="space-y-3">
                    @foreach ($settings['keys'] as $entry)
                        @php
                            $isNew = session('created_api_key_id') === $entry['id'];
                        @endphp
                        <li class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 {{ $isNew ? 'ring-2 ring-emerald-300' : '' }}">
                            <div class="flex items-center gap-2">
                                <div class="w-36 shrink-0">
                                    <p class="truncate text-sm font-medium text-slate-900" title="{{ $entry['name'] }}">{{ $entry['name'] }}</p>
                                    <p class="text-xs text-slate-500" dir="ltr">
                                        {{ \Illuminate\Support\Carbon::parse($entry['created_at'])->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                                    </p>
                                </div>
                                <input
                                    id="api-key-{{ $entry['id'] }}"
                                    type="text"
                                    class="ui-input min-w-0 flex-1 font-mono text-xs"
                                    value="{{ $entry['key'] }}"
                                    readonly
                                    dir="ltr"
                                    aria-label="کلید API {{ $entry['name'] }}"
                                >
                                <button
                                    type="button"
                                    class="ui-btn-secondary shrink-0"
                                    data-copy-target="#api-key-{{ $entry['id'] }}"
                                    title="کپی کلید"
                                >
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <rect x="9" y="9" width="13" height="13" rx="2"/>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                    </svg>
                                    <span data-copy-label>کپی</span>
                                </button>
                                <form method="POST" action="{{ route('admin.website-intake.keys.destroy', $entry['id']) }}" class="shrink-0" onsubmit="return confirm('این کلید حذف شود؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ui-btn-danger">حذف</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-copy-target]').forEach((button) => {
            button.addEventListener('click', async () => {
                const target = document.querySelector(button.getAttribute('data-copy-target'));
                if (!target) {
                    return;
                }

                const value = (target.value ?? target.textContent ?? '').trim();
                const label = button.querySelector('[data-copy-label]');
                const markCopied = () => {
                    button.classList.add('ring-2', 'ring-emerald-400');
                    if (label) {
                        label.textContent = 'کپی شد';
                    }
                    setTimeout(() => {
                        button.classList.remove('ring-2', 'ring-emerald-400');
                        if (label) {
                            label.textContent = 'کپی';
                        }
                    }, 1200);
                };

                try {
                    await navigator.clipboard.writeText(value);
                    markCopied();
                } catch (error) {
                    target.focus();
                    target.select();
                    document.execCommand('copy');
                    markCopied();
                }
            });
        });
    </script>
@endsection
