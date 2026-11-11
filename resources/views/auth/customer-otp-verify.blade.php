<x-layouts.auth title="تأیید کد ورود">
    <h2 class="mb-1 text-base font-semibold text-slate-900">ورود کد تأیید</h2>
    <p class="mb-5 text-sm text-slate-500">کد ارسال‌شده را وارد کنید.</p>

    <x-flash />

    <form method="POST" action="{{ route('customer.login.verify.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="otp" class="ui-label">کد</label>
            <input id="otp" type="text" name="otp" required maxlength="6" inputmode="numeric" autocomplete="one-time-code" class="ui-input">
        </div>
        <x-button class="w-full">تأیید</x-button>
    </form>
</x-layouts.auth>
