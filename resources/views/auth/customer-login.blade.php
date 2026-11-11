<x-layouts.auth title="ورود مشتری">
    <h2 class="mb-1 text-base font-semibold text-slate-900">ورود مشتری</h2>
    <p class="mb-5 text-sm text-slate-500">با نام کاربری و رمز عبور وارد شوید. نمونه: test / test</p>

    <x-flash />

    <form method="POST" action="{{ route('customer.login.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="username" class="ui-label">نام کاربری</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus class="ui-input" autocomplete="username">
        </div>
        <div>
            <label for="password" class="ui-label">رمز عبور</label>
            <input id="password" type="password" name="password" required class="ui-input" autocomplete="current-password">
        </div>
        <x-button class="w-full">ورود</x-button>
    </form>

    <p class="mt-4 text-center text-sm text-slate-500">
        <a class="ui-link" href="{{ route('customer.login.otp.form') }}">ورود با کد یک‌بارمصرف</a>
    </p>
</x-layouts.auth>
