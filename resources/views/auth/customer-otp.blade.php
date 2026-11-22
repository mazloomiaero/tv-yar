<x-layouts.auth title="ورود مشتری">
    <div class="auth-form-header">
        <span class="auth-form-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M7 3h7l3 3v15H7V3Z" />
                <path d="M10 12h4M10 16h4" />
            </svg>
            پنل مشتری
        </span>
        <h2 class="auth-form-title">ورود مشتری</h2>
        <p class="auth-form-subtitle">کد یک‌بارمصرف به شماره همراه ثبت‌شده ارسال می‌شود.</p>
    </div>

    <x-auth-method-tabs panel="customer" active="otp" />

    <x-flash />

    <form method="POST" action="{{ route('customer.login.otp') }}" class="auth-form" novalidate>
        @csrf
        <div>
            <label for="mobile" class="ui-label">شماره همراه</label>
            <input
                id="mobile"
                type="text"
                name="mobile"
                value="{{ old('mobile') }}"
                required
                autofocus
                maxlength="30"
                class="ui-input @error('mobile') border-red-500 @enderror"
                inputmode="tel"
                autocomplete="tel"
                dir="ltr"
                aria-invalid="{{ $errors->has('mobile') ? 'true' : 'false' }}"
                @error('mobile') aria-describedby="mobile-error" @enderror
            >
            @error('mobile')
                <p id="mobile-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <x-button class="auth-submit w-full">ارسال کد ورود</x-button>
    </form>
</x-layouts.auth>
