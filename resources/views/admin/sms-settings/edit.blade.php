@extends('layouts.admin')

@section('content')
    <x-page-header title="تنظیمات پیامک" subtitle="کلید API و پترن OTP سرویس فراز اس ام اس (FarazSMS) برای ورود مشتری و نمایش اعتبار." />

    <div class="dash-metric-grid mb-5">
        <x-dashboard.metric
            label="اعتبار پیامک"
            :value="$smsCredit !== null ? number_format($smsCredit) : '—'"
            icon="blue"
            :progress="$smsCredit !== null ? 72 : 12"
            tone="brand"
        >
            <x-slot:hint>
                @if ($smsCredit !== null)
                    موجودی فعلی از FarazSMS
                @elseif ($apiKeyConfigured)
                    دریافت اعتبار ناموفق بود
                @else
                    API Key پیکربندی نشده
                @endif
            </x-slot:hint>
        </x-dashboard.metric>
    </div>

    <form method="POST" action="{{ route('admin.sms-settings.update') }}">
        @csrf
        @method('PUT')
        <x-form-shell kicker="FarazSMS" title="اعتبارنامه سرویس پیامک" subtitle="Api-Key پنل فراز را اینجا بگذارید. خالی گذاشتن API Key مقدار فعلی را نگه می‌دارد.">
            <div>
                <label for="api_key" class="ui-label">Api-Key</label>
                <input
                    id="api_key"
                    type="password"
                    name="api_key"
                    value=""
                    maxlength="255"
                    autocomplete="off"
                    placeholder="{{ $apiKeyConfigured ? 'برای تغییر، کلید جدید را وارد کنید' : 'کلید API را وارد کنید' }}"
                    class="ui-input"
                >
                @if ($apiKeyHint)
                    <p class="mt-2 text-xs text-slate-500">کلید فعلی: <span class="font-mono" dir="ltr">{{ $apiKeyHint }}</span></p>
                @endif
                @error('api_key')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pattern_code" class="ui-label">کد پترن OTP</label>
                <input
                    id="pattern_code"
                    type="text"
                    name="pattern_code"
                    value="{{ old('pattern_code', $patternCode) }}"
                    maxlength="100"
                    class="ui-input"
                    dir="ltr"
                >
                <p class="mt-2 text-xs text-slate-500">کد پترن تأییدشده از پنل فراز (مثلاً SJ3FgPrE0C).</p>
                @error('pattern_code')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="otp_param" class="ui-label">نام متغیر پترن</label>
                <input
                    id="otp_param"
                    type="text"
                    name="otp_param"
                    value="{{ old('otp_param', $otpParam) }}"
                    maxlength="50"
                    class="ui-input"
                    dir="ltr"
                >
                <p class="mt-2 text-xs text-slate-500">نام متغیر داخل پترن (معمولاً code) باید دقیقاً مطابق پنل باشد.</p>
                @error('otp_param')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="line_number" class="ui-label">خط ارسال</label>
                <input
                    id="line_number"
                    type="text"
                    name="line_number"
                    value="{{ old('line_number', $lineNumber) }}"
                    maxlength="30"
                    class="ui-input"
                    dir="ltr"
                >
                <p class="mt-2 text-xs text-slate-500">شماره خط اختصاصی پنل (پیش‌فرض 90008361).</p>
                @error('line_number')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <x-slot:actions>
                <x-button>ذخیره</x-button>
                <x-button variant="secondary" href="{{ route('admin.dashboard') }}">بازگشت به داشبورد</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
