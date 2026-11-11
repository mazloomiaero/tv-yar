<x-layouts.auth title="ورود مشتری">
    <h2 class="mb-1 text-base font-semibold text-slate-900">ورود مشتری</h2>
    <p class="mb-5 text-sm text-slate-500">کد یک‌بارمصرف به شماره همراه ارسال می‌شود.</p>
    <p class="mb-5 text-center text-sm">
        <a class="ui-link" href="{{ route('customer.login') }}">ورود با نام کاربری و رمز عبور</a>
    </p>

    <x-flash />

    <form method="POST" action="{{ route('customer.login.otp') }}" class="space-y-4">
        @csrf
        <div>
            <label for="mobile" class="ui-label">شماره همراه</label>
            <input id="mobile" type="text" name="mobile" value="{{ old('mobile') }}" required autofocus maxlength="30" class="ui-input" inputmode="tel" autocomplete="tel">
        </div>
        <x-button class="w-full">ارسال کد</x-button>
    </form>
</x-layouts.auth>
