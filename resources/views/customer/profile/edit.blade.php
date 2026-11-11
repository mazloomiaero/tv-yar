@extends('layouts.customer')

@section('content')
    <x-page-header title="پروفایل" subtitle="مشخصات و رمز ورود پنل مشتری را اینجا تغییر دهید." />

    <form method="POST" action="{{ route('customer.profile.update') }}">
        @csrf
        @method('PUT')
        <x-form-shell kicker="حساب" title="ویرایش پروفایل" subtitle="اطلاعات تماس و ورود را به‌روز کنید.">
            <div>
                <x-field-label for="customer_code">کد مشتری</x-field-label>
                <input id="customer_code" type="text" value="{{ $customer->customer_code }}" class="ui-input" disabled>
                <p class="mt-1 text-xs text-slate-500">کد مشتری را فقط کارکنان می‌توانند تغییر دهند.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-field-label for="first_name" :required="true">نام</x-field-label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $customer->first_name) }}" maxlength="100" required class="ui-input">
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-field-label for="last_name" :required="true">نام خانوادگی</x-field-label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $customer->last_name) }}" maxlength="100" required class="ui-input">
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-field-label for="mobile" :required="true">شماره همراه</x-field-label>
                    <input id="mobile" type="text" name="mobile" value="{{ old('mobile', $customer->mobile) }}" maxlength="30" required class="ui-input">
                    @error('mobile')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-field-label for="mobile_secondary">همراه دوم</x-field-label>
                    <input id="mobile_secondary" type="text" name="mobile_secondary" value="{{ old('mobile_secondary', $customer->mobile_secondary) }}" maxlength="30" class="ui-input">
                    @error('mobile_secondary')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-field-label for="email">ایمیل</x-field-label>
                    <input id="email" type="text" name="email" value="{{ old('email', $customer->email) }}" maxlength="150" class="ui-input">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-field-label for="national_id">کد ملی</x-field-label>
                    <input id="national_id" type="text" name="national_id" value="{{ old('national_id', $customer->national_id) }}" maxlength="20" class="ui-input">
                    @error('national_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <fieldset class="ui-fieldset">
                <legend class="ui-legend">ورود به پنل مشتری</legend>
                <div class="space-y-4">
                    <div>
                        <x-field-label for="portal_username" :required="$customer->user_id !== null">نام کاربری</x-field-label>
                        <input
                            id="portal_username"
                            type="text"
                            name="portal_username"
                            value="{{ old('portal_username', $customer->user?->username ?? '') }}"
                            maxlength="255"
                            @required($customer->user_id !== null)
                            class="ui-input"
                            autocomplete="username"
                        >
                        @error('portal_username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-field-label for="portal_password">رمز عبور</x-field-label>
                            <div class="ui-password">
                                <input
                                    id="portal_password"
                                    type="password"
                                    name="portal_password"
                                    class="ui-input"
                                    autocomplete="new-password"
                                >
                                <button type="button" data-password-toggle class="ui-password-toggle" aria-label="نمایش رمز عبور">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                            @if ($customer->user_id !== null)
                                <p class="mt-1 text-xs text-slate-500">برای حفظ رمز فعلی خالی بگذارید.</p>
                            @else
                                <p class="mt-1 text-xs text-slate-500">برای ساخت ورود با نام کاربری و رمز، هر دو را پر کنید.</p>
                            @endif
                            @error('portal_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-field-label for="portal_password_confirmation">تکرار رمز عبور</x-field-label>
                            <div class="ui-password">
                                <input
                                    id="portal_password_confirmation"
                                    type="password"
                                    name="portal_password_confirmation"
                                    class="ui-input"
                                    autocomplete="new-password"
                                >
                                <button type="button" data-password-toggle class="ui-password-toggle" aria-label="نمایش تکرار رمز عبور">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <x-slot:actions>
                <x-button>ذخیره</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <script>
        (function () {
            document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
                if (button.dataset.bound === '1') {
                    return;
                }
                button.dataset.bound = '1';
                button.addEventListener('click', function () {
                    var wrap = button.parentElement;
                    var input = wrap ? wrap.querySelector('input') : null;
                    if (!input) {
                        return;
                    }
                    var hidden = input.getAttribute('type') === 'password';
                    input.setAttribute('type', hidden ? 'text' : 'password');
                    button.setAttribute('aria-label', hidden ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور');
                });
            });
        })();
    </script>
@endsection