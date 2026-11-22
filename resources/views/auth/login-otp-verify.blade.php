@php
    $mobile = session('staff_otp_mobile');
    $mobileHint = is_string($mobile) && strlen($mobile) >= 4
        ? str_repeat('•', max(0, strlen($mobile) - 4)).substr($mobile, -4)
        : null;
@endphp

<x-layouts.auth title="تأیید کد ورود کارکنان">
    <div class="auth-form-header">
        <span class="auth-form-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M7 3h7l3 3v15H7V3Z" />
                <path d="M10 12h4M10 16h4" />
            </svg>
            پنل کارکنان
        </span>
        <h2 class="auth-form-title">ورود کد تأیید</h2>
        <p class="auth-form-subtitle">
            @if ($mobileHint)
                کد ارسال‌شده به <span class="font-mono" dir="ltr">{{ $mobileHint }}</span> را وارد کنید.
            @else
                کد یک‌بارمصرف ارسال‌شده را وارد کنید.
            @endif
        </p>
    </div>

    <x-auth-method-tabs panel="staff" active="otp" />

    <x-flash />

    <form method="POST" action="{{ route('login.verify.store') }}" class="auth-form" novalidate>
        @csrf
        <div>
            <label for="otp" class="ui-label">کد یک‌بارمصرف</label>
            <input
                id="otp"
                type="text"
                name="otp"
                required
                maxlength="6"
                inputmode="numeric"
                autocomplete="one-time-code"
                autofocus
                class="ui-input @error('otp') border-red-500 @enderror"
                dir="ltr"
                aria-invalid="{{ $errors->has('otp') ? 'true' : 'false' }}"
                @error('otp') aria-describedby="otp-error" @enderror
            >
            @error('otp')
                <p id="otp-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <x-button class="auth-submit w-full">تأیید و ورود</x-button>
    </form>

    <p class="mt-4 text-center text-sm text-slate-500">
        کد نرسید؟
        <a class="ui-link" href="{{ route('login.otp.form') }}">ارسال مجدد</a>
    </p>
</x-layouts.auth>
